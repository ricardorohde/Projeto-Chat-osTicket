<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

$email=Format::input($_POST['luser']?:$_GET['e']);
$passwd=Format::input($_POST['lpasswd']?:$_GET['t']);

$content = Page::lookup(Page::getIdByType('banner-client'));

if ($content) {
    list($title, $body) = $ost->replaceTemplateVariables(
        array($content->getName(), $content->getBody()));
} else {
    $title = __('Sign In');
    $body = __('To better serve you, we encourage our clients to register for an account and verify the email address we have on record.');
}

?>
<h1 style="text-align: center;"><?php echo Format::display($title); ?></h1>
<p><?php echo Format::display($body); ?></p>
<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>
    <div class="well">
        <div class="row">
            <div class="col-md-5" style="border-right: rgba(171, 171, 171, 0.63); border-style: solid; border-bottom: none; border-left: none; border-top: none; ">
                <strong><?php echo Format::htmlchars($errors['login']); ?></strong>
                <div class="row">
                    <div class="col-md-10">
                        <input id="username" placeholder="<?php echo __('Email or Username'); ?>" type="text" name="luser" size="30" class="form-control" value="<?php echo $email; ?>">
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-md-10">
                        <input id="passwd" placeholder="<?php echo __('Password'); ?>" type="password" name="lpasswd" size="30" class="form-control" value="<?php echo $passwd; ?>"></td>
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-md-10">
                        <p style="text-align: right;">
                            <input class="btn btn-primary" type="submit" value="<?php echo __('Sign In'); ?>" style="color:#fff !important;">
                    <?php if ($suggest_pwreset) { ?>
                            <a style="padding-top:4px;display:inline-block;" href="pwreset.php"><?php echo __('Forgot My Password'); ?></a>
                    <?php } ?>
                        </p>
                    </div>
                </div>

            </div>
            <div class="col-md-7">
                <?php
                $ext_bks = array();
                foreach (UserAuthenticationBackend::allRegistered() as $bk)
                    if ($bk instanceof ExternalAuthentication)
                        $ext_bks[] = $bk;

                if (count($ext_bks)) {
                    foreach ($ext_bks as $bk) { ?>
                        <div class="external-auth"><?php $bk->renderExternalLink(); ?></div><?php
                    }
                }

                if ($cfg && $cfg->isClientRegistrationEnabled()) {
                    if (count($ext_bks)) echo '<hr style="width:70%"/>'; ?>
                        <div style="margin-bottom: 5px">
                            <?php echo __('Not yet registered?'); ?> <a href="account.php?do=create"><?php echo __('Create an account'); ?></a>
                        </div>
                <?php } ?>

                <div>
                    <b><?php echo __("I'm an agent"); ?></b> —
                    <a href="<?php echo ROOT_PATH; ?>scp/"><?php echo __('sign in here'); ?></a>
                </div>
            </div>

        </div>
    </div>
</form>
<br>
<p>
<?php if ($cfg && !$cfg->isClientLoginRequired()) {
    echo sprintf(__('If this is your first time contacting us or you\'ve lost the ticket number, please %s open a new ticket %s'),
        '<a href="open.php">', '</a>');
} ?>
</p>
