<?php
require_once  BASE_PATH . '/config.php';

class Database {

    // thuoc tinh static
    private static ?mysqli $conn = null;

    // lay hoac tao object mysqli (singleton)
    public static function conn():mysqli{
        // neu chua co object thi tao moi
        if (self::$conn === null) {
            // bat che do quang ngoai le de debug easy
            mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);

            // tao object mysqli
            self::$conn = new mysqli(DB_HOST,DB_USER, DB_PASS,DB_NAME);
            self::$conn->set_charset(DB_CHARSET);
            // new host | user |    pass sai thi se nem mysqli exception

        }

        return self::$conn;
    }


    // chay cau len sql thuan ko co variable truyen them
    public static function query(string $query):mysqli_result{
        return self::conn()->query($query);
    }


    // prepare statement
    public static function prepare(string $sql, string $types = '', array $params = []):mysqli_stmt{
        $stmt = self::conn()->prepare($sql);
        if ($types && $params ) { // check rong, neu 1 trong 2 rong thi ko thuc hien
            // su dung ...param de tach mang thanh tung parameter
            $stmt->bind_param($types, ...$params);
        }

        return $stmt; // caller se execute() hoac  get_result()
    }

    // auto close connect
    public function __destruct()
    {
        self::conn()->close();
    }


}