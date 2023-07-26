<?php
require_once 'connector.php';

if( ! session_id() || session_id() == '' || !isset($_SESSION) ) {
    session_start();
}
if(isset($_SESSION['authorized']) && !empty($_SESSION['authorized'])){
    if($_SESSION['authorized'] == true){
        if(mo_saml_is_customer_license_verified()){
            header("Location: setup.php");
            exit();
        } else {
            header("Location: account.php");
            exit();
        }
    }
}
if(isset($_POST['option']) && $_POST['option'] == 'admin_login'){
    $email='';
    $password = '';
    if(isset($_POST['email']) && !empty($_POST['email']))
        $email = $_POST['email'];
    if(isset($_POST['password']) && !empty($_POST['password']))
        $password = $_POST['password'];
    if(!empty($password)){
        $password = mo_get_pw_hash($password);
    }
        // $str='';
        // if((file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper'. DIRECTORY_SEPARATOR .'data'. DIRECTORY_SEPARATOR .'credentials.json')))
        //     $str = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper'. DIRECTORY_SEPARATOR .'data'. DIRECTORY_SEPARATOR .'credentials.json');
        $pw_check = DB::get_option('mo_saml_admin_pw');

        // $credentials_array = json_decode($str, true);
        
        // $password_check = '';
        // if(is_array($credentials_array))
        //     if(array_key_exists($email, $credentials_array))
        //         $password_check = $credentials_array[$email];
        //     else {
        //         $_SESSION['invalid_credentials'] = true;

        //     }

        if($email !== DB::get_option('mo_saml_admin_email')){
            $_SESSION['invalid_credentials'] = true;
        }

        if(!empty($pw_check)){
            if($password === $pw_check and $email === DB::get_option('mo_saml_admin_email')){
                
                if(!isset($_SESSION['authorized']) || $_SESSION['authorized'] != true){
                    $_SESSION['authorized'] = true;
                }
                $_SESSION['admin_email'] = $email;
                if(mo_saml_is_customer_license_verified()){
                    header("Location: setup.php");
                    exit();
                } else {
                    header("Location: account.php");
                    exit();
                }
                
            } else {

                echo '<form method="post" action="" name="mo_login_form">
                <input type="hidden" name="option" value="mo_saml_verify_customer">
                <input type="hidden" name="email" value="' . $email .'">
                <input type="hidden" name="password" value="'.$password.'">
                </form>
                
                <script>
                document.forms[\'mo_login_form\'].submit();
                </script>';
                $_SESSION['invalid_credentials'] = true;

            }
        }
    
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="includes/css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="includes/css/fonts/font-awesome.min.css">
    <title>Login - miniOrange Admin</title>
  </head>
  <body>
  <section class="material-half-bg">
      <div class="cover"></div>
    </section>
    <section class="login-content">
      <div class="logo">
        <h1><img src="resources/images/logo_large.png"></h1>
      </div>
      <div class="col-md-5">
          <div class="tile">
            <h3 class="tile-title">Login with miniOrange</h3>
            <form class="login_form" method="POST" action="">
            <input type="hidden" name="option" 
            <?php if(mo_saml_is_customer_registered()){
                ?>
                value = "admin_login"
                <?php
            } else { ?>
                value="mo_saml_verify_customer"
            <?php
            }
            ?>>
            <div class="tile-body">
              <div class="form-group row">
                  <label class="control-label col-md-3">Email</label>
                  <div class="col-md-8">
                    <input class="form-control col-md-10" type="email" name="email" placeholder="Enter email address" required>
                  </div>
                </div>
                <div class="form-group row">
                <label class="control-label col-md-3">Password</label>
                <div class="col-md-8">
                  <input class="form-control col-md-10" type="password" name="password" id="password" placeholder="Enter a password" minlength="6" required>
                </div>
                </div>
            </div>
            <div class="tile-footer">
              <div class="row">
                <div class="col-md-8 col-md-offset-3">
                <button class="btn btn-primary" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Login</button>
                </div>
              </div>
            </div>
            </form>
          </div>
        </div>
    </section>
      

    <!-- Essential javascripts for application to work-->
    <script src="includes/js/jquery-3.2.1.min.js"></script>
    <script src="includes/js/popper.min.js"></script>
    <script src="includes/js/bootstrap.min.js"></script>
    <script src="includes/js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="includes/js/plugins/pace.min.js"></script>
    <script type="text/javascript" src="includes/js/plugins/bootstrap-notify.min.js"></script>
    <script type="text/javascript" src="includes/js/plugins/sweetalert.min.js"></script>
    <?php
    if(isset($_SESSION['invalid_credentials']) && !empty($_SESSION['invalid_credentials'])){
        if($_SESSION['invalid_credentials'] === true){
            echo'<script>
                $(document).ready(function(){
                $.notify({
                    title: "ERROR: ",
                    message: "Invalid username or password",
                    icon: \'fa fa-times\' 
                },{
                    type: "danger"
                });
            });
            </script>';
            unset($_SESSION['invalid_credentials']);
        }
    }
    ?>
  </body>
</html>