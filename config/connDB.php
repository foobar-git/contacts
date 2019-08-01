<?php

// Connecting to MySQL database using PHP Data Objects (PDO) extension

function connectToDB ($connect, $server, $db, $user, $pswd) {
    if ($connect) {
        try {
            $conn = new PDO("mysql:host=$server;dbname=$db", $user, $pswd);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected to database.";
        }
        catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
    else {
        $conn = null;
        echo "Disconnected from database.";
    }
}

function searchContacts ($flag, $server, $db, $tbl, $user, $pswd, $name, $surname, $number) {
    echo "<table id='info' style='border: solid 1px white;'>";
    echo "<tr><th>&nbsp;Name</th><th>&nbsp;Surname</th><th>&nbsp;Number</th></tr>";
    
    class TableRows extends RecursiveIteratorIterator {

        function __construct($it) {
            parent::__construct($it, self::LEAVES_ONLY); 
        }

        function current() {
            return "<td style='width:150px;border:solid 1.5px green;'>" . parent::current(). "</td>";
        }

        function beginChildren() {
            echo "<tr>"; 
        }
        
        function endChildren() {
            echo "</tr>" . "\n";
        } 
    }

    try {
        switch ($flag) {
            case 'all': // button: List all contacts
                $sql = "SELECT Name, Surname, Number FROM `$tbl` ORDER BY Name";
                break;
                
            case 'search': // button: Search (contacts)
                if ( empty($number) ) $sql = "SELECT Name, Surname, Number FROM `$tbl` WHERE Name=? OR Surname=? ?";
                else $sql = "SELECT Name, Surname, Number FROM `$tbl` WHERE Name=? OR Surname=? OR Number=?";
                break;
                
            case 'dev no number': // list contacts with no telephone numbers
                $sql = "SELECT Name, Surname, Number FROM `$tbl` WHERE Name=? OR Surname=? OR Number=? ORDER BY Name";
                break;
                
            default:
                echo "No such search-operation.";
        }
            
        $conn = new PDO("mysql:host=$server;dbname=$db", $user, $pswd);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare($sql); 
        $stmt->execute([$name, $surname, $number]);
        // set the resulting array to associative
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        // display response from database
        foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) { 
            echo $v;
        }
    }
    catch(PDOException $e) {
        echo "Contact not found.<br>";
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
    echo "</table>";
}

function getContactID ($server, $db, $tbl, $user, $pswd, $name, $surname, $number) {
    try {
        if ( empty($flag) ) $sql = "SELECT ID FROM `$tbl` WHERE Name=? OR Surname=? ?";
        else $sql = "SELECT ID FROM `$tbl` WHERE Name=? OR Surname=? OR Number=?";
        
        $conn = new PDO("mysql:host=$server;dbname=$db", $user, $pswd);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        //$sql = "SELECT ID FROM `$tbl` WHERE Name=? OR Surname=? OR Number=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $surname, $number]);

        // get response from database (default: array)
        $id_db = $stmt->fetch();
        $id = $id_db[0];
    }
    catch(PDOException $e) {
        echo "Error: $sql <br>" . $e->getMessage();
    }
    
    $conn = null;
    return $id;
}

function getContactsStats ($server, $db, $tbl, $user, $pswd) { //dev tools
    try {
        $conn = new PDO("mysql:host=$server;dbname=$db", $user, $pswd);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $sql = "SELECT COUNT(ID) AS NumOfContacts FROM contacts";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // get response from database (default: array)
        $stats_db = $stmt->fetch();
        $stats1 = $stats_db[0];
        
        $sql = "SELECT COUNT(ID) AS EmptyNumbers FROM contacts WHERE Number IS NULL OR Number = ''";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // get response from database (default: array)
        $stats_db = $stmt->fetch();
        $stats2 = $stats_db[0];
        
        echo "Number of contacts: $stats1 <br>";
        echo "Contacts with empty telephone number: $stats2 <br>";
    }
    catch(PDOException $e) {
        echo "Error: $sql <br>" . $e->getMessage();
    }
    
    $conn = null;
}

function updateContact ($id, $server, $db, $tbl, $user, $pswd, $name, $surname, $number) {
    try {
        $conn = new PDO("mysql:host=$server;dbname=$db", $user, $pswd);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $sql = "UPDATE `$tbl` SET Name='$name', Surname='$surname', Number='$number' WHERE ID='$id'";

        // using exec() because no results are returned from database
        $conn->exec($sql);
    }
    catch(PDOException $e) {
        echo "Contact was not updated.<br>";
        echo "Error: $sql <br>" . $e->getMessage();
    }
    
    $conn = null;
}

function saveContact ($server, $db, $tbl, $user, $pswd, $name, $surname, $number) {
    try {
        $conn = new PDO("mysql:host=$server;dbname=$db", $user, $pswd);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sql = "INSERT INTO `$tbl` (ID, Name, Surname, Number)
        VALUES (NULL, '$name', '$surname', '$number')";
        
        // using exec() because no results are returned from database
        $conn->exec($sql);

        echo "New record created.<br>";
        echo "$name $surname $number";
    }
    catch(PDOException $e) {
        echo "Contact was not saved.<br>";
        echo "Error: $sql <br>" . $e->getMessage();
    }
    
    $conn = null;
}

function deleteContactByNameSurname ($server, $db, $tbl, $user, $pswd, $name, $surname) {
    try {
        $conn = new PDO("mysql:host=$server;dbname=$db", $user, $pswd);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // count how many contacts there is in database before deleting a contact
        $sql = "SELECT COUNT(ID) AS NumOfContacts FROM contacts";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // get response from database (default: array)
        $stats_db = $stmt->fetch();
        $beforeDelete = $stats_db[0];
        
        // delete the contact
        $sql = "DELETE FROM `$tbl` WHERE Name=? AND Surname=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $surname]);
        
        // count how many contacts there is left in database after deleting a contact
        $sql = "SELECT COUNT(ID) AS NumOfContacts FROM contacts";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // get response from database (default: array)
        $stats_db = $stmt->fetch();
        $afterDelete = $stats_db[0];
        
        if ($afterDelete != $beforeDelete) echo "Contact was deleted.";
        else echo "Contact was not deleted.";
    }
    catch(PDOException $e) {
        echo "Error: $sql <br>" . $e->getMessage();
    }
    
    $conn = null;
}

function deleteContactByID ($id, $server, $db, $tbl, $user, $pswd) {
    try {
        $conn = new PDO("mysql:host=$server;dbname=$db", $user, $pswd);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $sql = "DELETE FROM `$tbl` WHERE ID='$id'";
    
        // using exec() because no results are returned from database
        $conn->exec($sql);
    }
    catch(PDOException $e) {
        echo "Error: $sql <br>" . $e->getMessage();
    }
    
    $conn = null;
}

?>