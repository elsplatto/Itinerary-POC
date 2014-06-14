<?php
include 'includes/admin-settings.php';
include '../includes/db.php';
include 'includes/global-admin-functions.php';
assessLogin($securityArrSuper);

if (isset($_GET['id']))
{
    $userId = $_GET['id'];
}
else
{
    header('Location: user-list.php');
}


function getUserDetails($id, $DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE)
{
    $mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    $stmt = $mysqli->prepare('SELECT firstname, lastname, username, password, role, email, is_valid FROM admin_users WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $username, $password, $role, $email, $is_valid);

    $results = array();
    while($stmt->fetch())
    {
        $results['firstname'] = $firstname;
        $results['lastname'] = $lastname;
        $results['username'] = $username;
        $results['password'] = $password;
        $results['role'] = $role;
        $results['email'] = $email;
        $results['is_valid'] = $is_valid;
    }

    $stmt->close();
    $mysqli->close();
    return $results;
}


$userDetails = getUserDetails($userId, $DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);


?>
<html>
<head>
    <title>Edit User - <?=$userDetails['firstname'] . ' '. $userDetails['lastname'] ?></title>
    <?php
    include 'includes/head.php';
    ?>
</head>
<body>
<section>
    <div class="row">
        <div class="large-12 columns">
            <a href="dashboard.php">Home</a>
            <h1>User</h1>
            <a href="user-list.php">< Back to User List</a>

        </div>
    </div>
</section>

<section>
    <form id="frmUser" name="frmUser" action="user-process.php" method="post" data-abide>
        <div class="row">
            <div class="large-12 columns">
                <input type="hidden" id="userId" name="userId" value="<?=$userId?>" />
                <label for="txtFirstname">First Name:
                    <input type="text" id="txtFirstname" name="txtFirstname" value="<?=$userDetails['firstname']?>" required />
                    <small class="error">Please enter users first name</small>
                </label>

                <label for="txtLastname">Last Name:
                    <input type="text" id="txtLastname" name="txtLastname" value="<?=$userDetails['lastname']?>" required />
                    <small class="error">Please enter users last name</small>
                </label>

                <label for="txtUsername">Username:
                    <input type="text" id="txtUsername" name="txtUsername" value="<?=$userDetails['username']?>" required />
                    <span class="ajaxCheck"></span>
                    <small class="error">Please enter a username</small>
                </label>


                <label for="selRole">Role:
                    <select id="selRole" name="selRole" required>
                        <option value="">Select role</option>
                        <option value="super" <?=strtolower($userDetails['role']) == 'super' ? 'selected="selected"' : '' ?>>Super User</option>
                        <option value="publisher" <?=strtolower($userDetails['role']) == 'publisher' ? 'selected="selected"' : '' ?>>Publisher</option>
                        <option value="author" <?=strtolower($userDetails['role']) == 'author' ? 'selected="selected"' : "" ?>>Author</option>
                        <option value="restricted" <?=strtolower($userDetails['role']) == 'restricted' ? 'selected="selected"' : '' ?>>Restricted</option>
                    </select>
                    <small class="error">Please select user's role</small>
                </label>


                <label for="txtEmail">Email:
                    <input type="email" id="txtEmail" name="txtEmail" value="<?=$userDetails['email']?>" required />
                    <small class="error">Please enter a valid email</small>
                </label>

                <label for="chkValid">Valid:
                    <input type="checkbox" id="chkValid" name="chkValid" value="1" <?=$userDetails['is_valid'] == 1 ? 'checked="checked"' : '' ?>/>
                </label>

                <input class="button" type="submit" value="Submit" />
            </div>
        </div>
    </form>
</section>

<?php
include 'includes/footer.php';
?>
<script src="../js/foundation/foundation.abide.js"></script>
<script>
$('#frmUser').foundation('abide');

$(function() {
    $('#txtUsername').blur(function(e)
    {
        var el = $(this);
        var username = el.val();
        var id = $('#userId').val();
        var indicator = el.next('.ajaxCheck');
        if (username !== '')
        {
            indicator.css({
                top: $(this).parent('label').height() / 2,
                left: $(this).outerWidth() + 10
            });

            $.ajax({
                type: 'POST',
                url: 'services/unique-username.php',
                dataType: 'json',
                data: {
                    username: username,
                    postedId: id
                },
                beforeSend: function()
                {
                    beforeUniqueCheckHandler(el);
                },
                success: function(data)
                {
                    successUniqueCheckHandler(data, el, indicator)
                }
            });
        }
        else
        {
            el.siblings('small').text('Please enter a username');
        }
    });

    function beforeUniqueCheckHandler(el) {
        el.siblings('.ajaxCheck').addClass('preloader');
    }

    function successUniqueCheckHandler(data,el,indicator)
    {
        var obj = JSON.parse(data);

        el.siblings('.ajaxCheck').removeClass('preloader');

        if (obj.success)
        {
            if (obj.unique)
            {
                //indicator.text('unique');
                el.siblings('.ajaxCheck').addClass('okay');
                el.siblings('.ajaxCheck').removeClass('problem');
                el.removeAttr('data-invalid');
                el.parent('label').removeClass('error');
                el.siblings('small').text('Please enter a username');
            }
            else
            {
                //indicator.text('duplicate');
                el.siblings('.ajaxCheck').addClass('problem');
                el.siblings('.ajaxCheck').removeClass('okay');
                el.attr('data-invalid','');
                el.parent('label').addClass('error');
                el.siblings('small').text('Your username is not unique');
            }
        }
    }
});
</script>
</body>
</html>