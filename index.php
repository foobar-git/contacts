<!DOCTYPE html>
<html>

<head>
    <script>
        // reload page if page is cached and the browser back button is pressed
        window.onpageshow = (page) => {
            if (page.persisted) window.location.reload();
        };
        
        // XMLHttpRequest function for sending http requests to server
        function requestClearFileContent (clear) {
            xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = () => {
                if (this.readyState == 4 && this.status == 200) {
                    //document.getElementById("txt").innerHTML = this.responseText;
                    //alert(this.responseText);
                }
            };
            
            let fpath = '';
            if (clear) fpath = "config/clear.php?clear=";
            if (clear == 'sleepThenClear') fpath = "config/sleepClear.php?clear=";
            
            xmlhttp.open("GET", fpath + clear, true);
            xmlhttp.send();
        }
        
        // check if the page was reloaded or refreshed then clear idi.ini
        function checkForPageReload () {
            if (performance.navigation.type == 1) {
                console.info("This page was reloaded");
                requestClearFileContent(true);
            } //else console.info("This page was not reloaded");
        }
    </script>
    
    <meta charset = "utf-8">
    <title>Contacts</title>
    <link rel="stylesheet" type="text/css" href="/zentest/contacts/css/style.css">

    <!-- Compiled and minified CSS: "Materialize CSS" -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!-- Materialize CSS Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
    <!-- Compiled and minified JavaScript: "Materialize CSS" -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <nav>
        <div class="nav-wrapper">
          <a href="http://testing.zenmasters.org/zentest/contacts" title = "Reload page" class="brand-logo">&nbsp;Contacts</a>
          <ul id="nav-mobile" class="right">
            <li><a href="/zentest/contacts">Home</a></li>
            <li><a href="/zentest/contacts/about.html">About</a></li>
          </ul>
        </div>
    </nav>

    <p style="text-align:left">
        &nbsp;&nbsp;&nbsp;&nbsp;Frontend: Materialize CSS + Vanilla JavaScript
        <br />
        &nbsp;&nbsp;&nbsp;&nbsp;Backend: PHP + MySQL
    </p>

<div class="container">
    
    <div class="inputPanel">
        <form class="col s6">
            <div>
                <div class="input-field col s5">
                    <i class="material-icons prefix">account_circle</i>
                    <input name="name" id="name" type="text">
                    <label for="name" style="color:white">Name</label>
                </div>
            </div>
            <div>
                <div class="input-field col s5">
                    <i class="material-icons prefix">account_circle</i>
                    <input name="surname" id="surname" type="text">
                    <label for="surname" style="color:white">Surname</label>
                </div>
            </div>
            <div>
                <div class="input-field col s5">
                    <i class="material-icons prefix">phone</i>
                    <input name="number" id="number" type="text">
                    <label for="number" style="color:white">Number</label>
                </div>
            </div>
            <div class="row">
                <button type="submit" name="submit" value="search" class="waves-effect waves-light btn">Search / Select</button>
                <button type="submit" name="submit" value="list" class="waves-effect waves-light btn">List all contacts</button>
            </div>
            <div class="row">
                <button type="submit" name="submit" value="save" class="waves-effect waves-light btn">Save</button>
                <button type="submit" name="submit" value="update" class="waves-effect waves-light btn">Update</button>
                <button type="reset" class="waves-effect waves-light btn">Reset</button>
                <button type="submit" name="submit" value="delete" class="waves-effect waves-light btn">Delete</button>
            </div>
            <hr />
            <div class="row">&nbsp;&nbsp;&nbsp;
                Development Tools:
                <br /><br />
                &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Contacts stats">
                <br /><br />
                &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Contacts with no number">
                <!--br /><br />
                &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Connect to DB"-->
                <br /><br />
                &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Disconnect from DB">
            </div>
        </form>
    </div>
    
    <!-- little "hack" to display it right -->
    <div class="row">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </div>

    <div class="main">
        <?php
            ////////////////////////////////////////////////////////////////////////////////
            // Main logic from here: ///////////////////////////////////////////////////////
        
            require __DIR__.'/config/connDB.php';
            require __DIR__.'/config/key.php';
            require 'functions.php';

            if (isset($_GET['submit'])) {
                $name = $_GET['name'];
                $surname = $_GET['surname'];
                $number = $_GET['number'];
                $operation = $_GET['submit'];

                switch ($operation) {
                    case 'list':
                        searchContacts('all', $servername, $database, $table, $username, $password, $name, $surname, $number);
                        // clear file content to prevent accidental contact info updates
                        clearFileContent(__DIR__.'/config/id.ini');
                        break;
                        
                    case 'search':
                        if ( empty($name) && empty($surname) && empty($number) ) echo "Input fields empty.<br>";
                        else {
                            searchContacts('search', $servername, $database, $table, $username, $password, $name, $surname, $number);
                            $id = getContactID($servername, $database, $table, $username, $password, $name, $surname, $number);
                            readWriteToFile(true, $id, __DIR__.'/config/id.ini');
                            ?>
                                <script>
                                    requestClearFileContent('sleepThenClear');
                                    setTimeout( () => {document.getElementById("info").innerHTML = "&nbsp;&nbsp;Session timed out. Please re-enter contact information."}, 30000); // 30 seconds sleep
                                </script>
                            <?php
                        }
                        break;
                        
                    case 'update':
                        $id = readWriteToFile(false, "", __DIR__.'/config/id.ini');
                        if ( (!empty($name) && !empty($number)) || (!empty($surname) && !empty($number)) ) {
                            updateContact($id, $servername, $database, $table, $username, $password, $name, $surname, $number);
                            if ( empty($id) ) echo "No contact selected.";
                            else {
                                echo "Contact was updated:<br>";
                                echo "$name $surname $number";
                            }
                        } else {
                            echo "Contact was not updated.<br>";
                            echo "To update a contact enter a name or a surname with a number.<br>";
                        }
                        break;
                        
                    case 'save':
                        if ( (!empty($name) && !empty($number)) || (!empty($surname) && !empty($number)) ) {
                            saveContact($servername, $database, $table, $username, $password, $name, $surname, $number);
                        }
                        else echo "To save a contact enter a name or a surname with a number.<br>";
                        break;
                        
                    case 'delete':
                        $id = readWriteToFile(false, "", __DIR__.'/config/id.ini');
                        if ( empty($id) ) {
                            if (!empty($name) && !empty($surname)) {
                                deleteContactByNameSurname($servername, $database, $table, $username, $password, $name, $surname);
                            } else echo "To delete a contact enter a name and a surname.<br>";
                        } else {
                            deleteContactByID($id, $servername, $database, $table, $username, $password);
                            echo "Contact deleted.";
                        }
                        break;
                        
                    // devtools
                    case 'Contacts stats':
                        getContactsStats($servername, $database, $table, $username, $password);
                        break;
                    case 'Contacts with no number':
                        searchContacts('dev no number', $servername, $database, $table, $username, $password, "", "", "");
                        // clear file content to prevent accidental contact info updates
                        clearFileContent(__DIR__.'/config/id.ini');
                        break;
                    //case 'Connect to DB':
                    //    connectToDB(true, $servername, $database, $username, $password);
                    //    break;
                    case 'Disconnect from DB':
                        connectToDB(false, "", "", "", "");
                        break;
                        
                    default:
                        echo "No such operation.";
                }
            }
        ?>
        <hr />
    </div>
</div>

    <script>
        /*document.getElementById('myForm').onsubmit = (e) => {
            e.preventDefault(); // prevent redirection after submit
        }*/
    </script>

</body>

</html>