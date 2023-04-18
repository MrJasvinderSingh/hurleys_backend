<?php
include("db.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    form {
        padding: 80px;
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1050;
        display: none;
        width: 100%;
        height: 100%;
        overflow: hidden;
        outline: 0;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);

    }

    .modal-content {
        position: relative;
        display: -ms-flexbox;

        -ms-flex-direction: column;
        flex-direction: column;

        pointer-events: auto;
        background-color: #fffbfb;
        background-clip: padding-box;
        border: 1pxsolidrgba(0, 0, 0, .2);
        border-radius: 0.3rem;
        outline: 0;
        margin-left: 35%;
        max-width: min-content;
    }

    .modal-header {
        display: -ms-flexbox;
        display: flex;
        -ms-flex-align: start;
        align-items: flex-start;
        -ms-flex-pack: justify;
        justify-content: center;
        padding: 1rem 1rem;
        border-bottom: 1px solid #dee2e6;
        border-top-left-radius: calc(0.3rem - 1px);
        border-top-right-radius: calc(0.3rem - 1px);
    }
</style>

<body>
    <div class="container">
        <form action="index.php" method="post" id="search" style="padding: 80px;">
            <label for="age">Age:</label>
            <input type="number" name="age" id="age" placeholder="enter your age" value="<?php if (isset($_POST['age'])) {
                                                                                                echo $_POST['age'];
                                                                                            } ?>">
            <label for="age">Gender:</label>
            <select name="gender" id="gender">
                <option value="">Select Gender</option>
                <option <?php if (isset($_POST['gender']) && $_POST['gender'] == 'Male')  echo "selected = 'selected'"; ?>>Male</option>
                <option <?php if (isset($_POST['gender']) && $_POST['gender'] == 'Female')  echo "selected = 'selected'"; ?>>Female</option>

            </select>
            <input type="submit" name="Search" value="Search" id="submit" required>
        </form>
    </div>
    <section>
        <table class="table" id="mytable">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>SELECT</th>
                    <th>FIRSTNAME</th>
                    <th>LASTNAME</th>
                    <th>EMAIL</th>
                    <th>PHONE</th>
                    <th>AGE</th>
                    <th>GENDER</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include("db.php");
                $cond = 1;
                if (isset($_POST['age']) && !empty($_POST['age']) && empty($_POST['gender'])) {
                    $cond = "age = " . $_POST['age'];
                } else if (isset($_POST['gender']) && !empty($_POST['gender']) && empty($_POST['age'])) {
                    $gender = $_POST['gender'];
                    $cond = "gender = '" . $gender . "'";
                } else if (isset($_POST['age'])  && !empty($_POST['age']) && isset($_POST['gender']) && !empty($_POST['gender'])) {
                    $gender = $_POST['gender'];
                    $cond = "age = " . $_POST['age'] . " AND gender = '" . $gender . "'";
                }
                $sql = "select * from users WHERE " . $cond;
                // echo $sql;
                $result = $mysqli->query($sql);
                foreach ($result as $result_each) {
                    echo '<tr>
                      <td>' . $result_each["id"] . '</td>
                      <td><input type="checkbox" id="select" name="select" /></td>
                      <td class="name" data-id ="' . $result_each["fcm"] . '">' . $result_each["firstname"] . '</td>
                      <td>' . $result_each["lastname"] . '</td>
                      <td>' . $result_each["email"] . '</td>
                      <td class="phone">' . $result_each["phone"] . '</td>
                      <td>' . $result_each["age"] . '</td>
                      <td>' . $result_each["gender"] . '</td>
                       </tr>';
                }
                ?>

            </tbody>

        </table>
    </section>

    <button class="btn btn-primary" id="btn">Send</button>
    <div id="dialog" class="modal">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5>contact</h5><br>
                <form action="" method="post" id="form" name="form">
                    <label for="title">title</label>
                    <input type="text" placeholder="title" id="title" name="title"><br>
                    <label for="message">Body</label>
                    <input type="comment" id="comment" name="comment" placeholder="something.."><br><br>
                    <input type="button" id="submitted" value="Send"><br>

                    <div class="selected-users"></div>
                    
                </form>
            </div>

        </div>
    </div>
    <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" />
    <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.3.min.js"></script>
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#mytable').DataTable();
        });
    </script>
    <script>
        var modal = $('#dialog');
        var btn = $("#btn");
        // var span = $(".close");
        $(document).ready(function() {
            btn.on('click', function() {
                var selected_users = '';
                var ifSelected = false;
                $("#mytable > tbody  > tr > td > input").each(function(e, data) {
                    if (data.checked) {
                        ifSelected = true;
                        selected_users += 'Name: ' + $(this).parent().parent().find(".name").text() + '<br>';
                        selected_users += 'Phone: ' + $(this).parent().parent().find(".phone").text() + '<br>';

                    }
                });
                if (ifSelected) {
                    $('.selected-users').html('<b>Selected Users:</b><br/>' + selected_users);
                    modal.show();
                } else {
                    alert('Please select users.');
                }
            });

        });
        $('body').bind('click', function(e) {
            if ($(e.target).hasClass("modal")) {
                modal.hide();
            }
        })
    </script>
    <script>
        $(document).ready(function() {
            $("#submitted").click(function() {
                var fcm = [];
                $("#mytable > tbody  > tr > td > input").each(function(e, data) {
                    if (data.checked) {
                        
                        fcm.push($(this).parent().parent().find(".name").data('id'));
                    }
                });
                console.log(fcm);
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "https://fcm.googleapis.com/fcm/send");

                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Content-Type", "application/json");

                xhr.onload = () => console.log(xhr.responseText);
                
                let data = {
                    "to":fcm,
                    notification:{
                     "body":$("#comment").val(),
                     "title": $("#title").val(),
                      },
                    };
                
               console.log(data);
                xhr.send(data);
            });
        })
    </script>
    

</body>

</html>