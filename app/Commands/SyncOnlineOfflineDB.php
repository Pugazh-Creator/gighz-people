<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use mysqli;

class SyncOnlineOfflineDB extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'fetch:data';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        // Hostinger Database Credentials
        $hostinger_host = "srv827.hstgr.io";  // Change to your Hostinger DB host
        $hostinger_user = "u853418576_Test";
        $hostinger_pass = "Gighz@_1";
        $hostinger_db   = "u853418576_Test";

        // Local MySQL Database Credentials
        $local_host = "localhost";
        $local_user = "root"; // Change if you use a different user
        $local_pass = "root";     // Enter your local DB password if any
        $local_db   = "demofetch";

        // Connect to Hostinger MySQL Database
        $hostinger_conn = new mysqli($hostinger_host, $hostinger_user, $hostinger_pass, $hostinger_db);
        if ($hostinger_conn->connect_error) {
            die("❌ Hostinger Connection Failed: " . $hostinger_conn->connect_error);
        }

        // Connect to Local MySQL Database
        $local_conn = new mysqli($local_host, $local_user, $local_pass, $local_db);
        if ($local_conn->connect_error) {
            die("❌ Local MySQL Connection Failed: " . $local_conn->connect_error);
        }

        // Fetch All Tables from Hostinger Database
        $tables = [];
        $table_query = $hostinger_conn->query("SHOW TABLES");
        if ($table_query) {
            while ($row = $table_query->fetch_array()) {
                $tables[] = $row[0];  // Get table names
            }
        }

        foreach ($tables as $table) {
            // echo "<br>🔹 Syncing Table: $table <br>";

            // Check if table exists in Local MySQL
            $table_check = $local_conn->query("SHOW TABLES LIKE '$table'");
            if ($table_check->num_rows == 0) {
                echo "⚠️ Table $table does not exist in Local DB. Creating it...<br>";

                // Get table creation query from Hostinger
                $table_create_query = $hostinger_conn->query("SHOW CREATE TABLE `$table`");
                $create_table_sql = $table_create_query->fetch_assoc()["Create Table"];

                // Execute the table creation query in local DB
                if ($local_conn->query($create_table_sql)) {
                    echo "✅ Table $table created successfully!<br>";
                } else {
                    echo "❌ Error creating table $table: " . $local_conn->error . "<br>";
                    continue; // Skip inserting data if table creation fails
                }
            }

            // Fetch Data from Hostinger Database
            $sql = "SELECT * FROM `$table`";
            $result = $hostinger_conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $columns = array_keys($row);
                    $values = array_values($row);

                    // Escape values for SQL insertion
                    $escaped_values = array_map([$local_conn, 'real_escape_string'], $values);

                    // Convert values to SQL format
                    $columns_str = implode("`, `", $columns);
                    $values_str = "'" . implode("', '", $escaped_values) . "'";

                    // Build ON DUPLICATE KEY UPDATE query
                    $update_str = "";
                    foreach ($columns as $index => $column) {
                        $update_str .= "`$column` = VALUES(`$column`), ";
                    }
                    $update_str = rtrim($update_str, ", ");

                    // Insert or Update Query
                    $insert_sql = "INSERT INTO `$table` (`$columns_str`) VALUES ($values_str) 
                           ON DUPLICATE KEY UPDATE $update_str";

                    if ($local_conn->query($insert_sql)) {
                        echo "✅ Synced Record in $table <br>";
                    } else {
                        echo "❌ Error in $table: " . $local_conn->error . "<br>";
                    }
                }
            } else {
                echo "⚠️ No data found in $table <br>";
            }
        }

        // Close Database Connections
        $hostinger_conn->close();
        $local_conn->close();

        echo "<br>🚀 All Tables Synced Successfully!";
    }
}
