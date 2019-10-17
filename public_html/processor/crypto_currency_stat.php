<?php 

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// set the handler
set_error_handler('customError');

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/library/Requests.php';
require_once '../includes/utils.php'; // include utility liberary

// make sure Requests can load internal classes
Requests::register_autoloader();

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die(); // stop script
}

date_default_timezone_set('UTC');

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: '.$conn->connect_error);
    }

    // check if cryptocurrency statistics table is updated
    $query = 'SELECT updating, lastUpdatedTime FROM crypto_currency_update_state LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    $req_crypto_data; // crypto data to send to client
    $insert_table_row = false;

    // check if is empty
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($is_updating, $last_updated_time);
        $stmt->fetch();

    } else { // table is empty
        $last_updated_time = 0;
        $is_updating = 0;
        $insert_table_row = true;
    }

    $stmt->close();

    // check if data in the table is still up to date or been updated by another request
    if (time() < $last_updated_time || $is_updating == 1) {
        // fetch data from database
        $query = 
            'SELECT A.cryptoID, B.symbol, B.name, A.currency, A.price, A.marketCap, A.supply, A.changePCT, A.lastVolume, A.lastUpdate 
             FROM crypto_currency_prices AS A LEFT JOIN crypto_currencies AS B ON A.cryptoID = B.id ORDER BY A.cryptoID ASC';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = 0;
        $req_crypto_data = [];

        while ($counter < $result->num_rows) {
            $row1 = $result->fetch_assoc();
            $row2 = $result->fetch_assoc();
            $req_crypto_data[] = 
                [
                    'crypto_name' => $row1['name'],
                    'crpto_symbol' => $row1['symbol'],
                    strtolower($row1['currency']) => 
                        [
                            'price' => $row1['price'],
                            'last_update' => $row1['lastUpdate'],
                            'mkt_cap' => $row1['marketCap'],
                            'supply' => $row1['supply'],
                            'change_pct_hour' => $row1['changePCT'],
                            'last_vol' => $row1['lastVolume']
                        ],
                    strtolower($row2['currency']) => 
                        [
                            'price' => $row2['price'],
                            'last_update' => $row2['lastUpdate'],
                            'mkt_cap' => $row2['marketCap'],
                            'supply' => $row2['supply'],
                            'change_pct_hour' => $row2['changePCT'],
                            'last_vol' => $row2['lastVolume']
                        ]
                ];

            $counter += 2; // increment counter by two
        }

        // send result to client
        echo json_encode($req_crypto_data);

    } else { // fetch crypto data using API
        // notify other request that data is updating
        if ($insert_table_row) {
            $query = 'INSERT INTO crypto_currency_update_state (updating, lastUpdatedTime) VALUES(?, ?)';

        } else {
            $query = 'UPDATE crypto_currency_update_state SET updating = ?, lastUpdatedTime = ? LIMIT 1';
        }
        
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('ii', $updating_state, $updated_time);
        $updating_state = 1;
        $updated_time = 0;
        $stmt->execute();

        try {
            // requset for crypto data
            list ($crypto_name_id, $crypto_symbols) = getCryptoNameIdAndSymbols($conn);
            $req_url = 'https://min-api.cryptocompare.com/data/pricemultifull?fsyms=' . $crypto_symbols . '&tsyms=USD,EUR';
            $headers = ['authorization' => 'Apikey ' . CRYPTOCOMPARE_API_KEY];
            $response = Requests::get($req_url, $headers);

        } catch (Exception $e) {
            resetDataUpdatingNotification($conn);

            // close connection to database
            $stmt->close();
            $conn->close();

            // return empty data to client
            echo '[]';
            exit;
        }

        // update crypto data after 2 minutes has elapsed
        $wait_time_update = 60 * 2;

        // check if request is successfully
        if ($response->success && $response->status_code == 200) {
            $crypto_data = json_decode($response->body, true); // decode to associative array

            // update "crypto_currency_update_state" table
            if ($insert_table_row) {
                $stmt->close(); // close previous statement
                $query = 'UPDATE crypto_currency_update_state SET updating = ?, lastUpdatedTime = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('ii', $updating_state, $updated_time);
                $updating_state = 0;
                $updated_time = time() + $wait_time_update;
                $stmt->execute();

            } else {
                $updating_state = 0;
                $updated_time = time() + $wait_time_update;
                $stmt->execute();
            }

            $stmt->close();

            try {
                $conn->begin_transaction(); // start transaction

                // delete all crypto data
                $query = 'TRUNCATE TABLE crypto_currency_prices';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->execute();
                $stmt->close();

                $query = 
                    'INSERT INTO crypto_currency_prices (cryptoID, currency, price, marketCap, supply, changePCT, lastVolume, lastUpdate)
                     VALUES(?, ?, ?, ?, ?, ?, ?, ?)';

                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('dsdddddd', $crypto_id, $currency, $price, $market_cap, $supply, $change_pct, $last_volume, $last_update);
                $req_crypto_data_list;

                foreach ($crypto_data['RAW'] as $key => $value) { // loop through crypto currency
                    // append data to send back to client
                    $req_crypto_data = 
                        [
                            'crypto_name' => $crypto_name_id[$key]['name'],
                            'crpto_symbol' => $key
                        ];

                    foreach ($value as $inner_key => $inner_value) { // loop through currency (example: Dollar etc)
                        // set data
                        $crypto_id = $crypto_name_id[$key]['id'];
                        $currency = $inner_key; // example: "USD"
                        $price = $inner_value['PRICE'];
                        $market_cap = $inner_value['MKTCAP'];
                        $supply = $inner_value['SUPPLY'];
                        $change_pct = $inner_value['CHANGEPCTHOUR'];
                        $last_volume = $inner_value['LASTVOLUME'];
                        $last_update = $inner_value['LASTUPDATE'];

                        $stmt->execute();

                        // append data to send back to client
                        $req_crypto_data = array_merge(
                            $req_crypto_data,
                            [
                                strtolower($inner_key) => 
                                    [
                                        'price' => $inner_value['PRICE'],
                                        'last_update' => $inner_value['LASTUPDATE'],
                                        'mkt_cap' => $inner_value['MKTCAP'],
                                        'supply' => $inner_value['SUPPLY'],
                                        'change_pct_hour' => $inner_value['CHANGEPCTHOUR'],
                                        'last_vol' => $inner_value['LASTVOLUME']
                                    ]
                            ]
                        );
                    }

                    $req_crypto_data_list[] = $req_crypto_data;
                }

                $conn->commit(); // commit all the transaction

                // close connection to database
                $stmt->close();
                $conn->close();

                // send data to client
                echo json_encode($req_crypto_data_list);

            } catch (Exception $e) {
                $conn->rollback(); // remove all queries from queue if error occured (undo)
            }

        } else { // handle request error
            resetDataUpdatingNotification($conn);
            
            // close connection to database
            $stmt->close();
            $conn->close();

            // return empty data to client
            echo '[]';
            exit;
        }
    }

} catch (mysqli_sql_exception $e) {
    echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;

} catch (Exception $e) { // catch other exception
    echo 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
}

// utility function to reset data is updating
function resetDataUpdatingNotification($db_connection) {
    $query = 'UPDATE crypto_currency_update_state SET updating = ?, lastUpdatedTime = ? LIMIT 1';
    $stmt = $db_connection->prepare($query); // prepare statement
    $stmt->bind_param('ii', $updating_state, $updated_time);
    $updating_state = 0;
    $updated_time = 0;
    $stmt->execute();
    $stmt->close();
}

// utility function for fetching supported cyrpto name, id and symbols
function getCryptoNameIdAndSymbols($db_connection) {
    $name_id = [];
    $symbols;

    $query = 'SELECT id, symbol, name FROM crypto_currencies';
    $stmt = $db_connection->prepare($query); // prepare statement
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $symbols[] = $row['symbol'];
        $name_id = array_merge(
            $name_id, [
                $row['symbol'] => [
                    'name' => $row['name'],
                    'id' => $row['id']
                ]
            ]
        );
    }

    $stmt->close();

    return [$name_id, implode(',', $symbols)];
}

?>