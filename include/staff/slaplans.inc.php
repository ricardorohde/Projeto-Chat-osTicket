<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die('Access Denied');

$qs = array();
$sql='SELECT * FROM '.SLA_TABLE.' sla WHERE 1';
$sortOptions=array('name'=>'sla.name','status'=>'sla.isactive','period'=>'sla.grace_period','date'=>'sla.created','updated'=>'sla.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'sla.name';

if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])]) {
    $order=$orderWays[strtoupper($_REQUEST['order'])];
}
$order=$order?$order:'ASC';

if($order_column && strpos($order_column,',')){
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(*) FROM '.SLA_TABLE.' sla ');
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);

$pageNav->setURL('slas.php', $qs);
//Ok..lets roll...create the actual query
$qstr .= '&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');
$query="$sql ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' '._N('SLA plan',
        'SLA plans' /* SLA is abbreviation for Service Level Agreement */,
        $total);
else
    $showing=__('No SLA plans found!' /* SLA is abbreviation for Service Level Agreement */);

?>

<div class="pull-left" style="">
 <h2><?php echo __('Service Level Agreements');?></h2>
</div>
<div class="pull-right flush-right" style="padding-top:5px;padding-right:5px;">
 <b><a href="slas.php?a=add" class="Icon input-button input-button-primary"><?php echo __('Add New SLA Plan');?></a></b></div>
<div class="clear"></div>
<form action="slas.php" method="POST" name="slas">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
    <caption><?php echo $showing; ?></caption>
<div class="table-responsive">
 <table class="table table-striped table-bordered" style="width: 100%;">
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th width="320"><a <?php echo $name_sort; ?> href="slas.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name');?></a></th>
            <th width="100"><a <?php echo $status_sort; ?> href="slas.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status');?></a></th>
            <th width="130"><a <?php echo $period_sort; ?> href="slas.php?<?php echo $qstr; ?>&sort=period"><?php echo __('Grace Period (hrs)');?></a></th>
            <th width="120" nowrap><a <?php echo $created_sort; ?>href="slas.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Date Added');?></a></th>
            <th width="150" nowrap><a <?php echo $updated_sort; ?>href="slas.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated');?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $total=0;
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($res && db_num_rows($res)):
            $defaultId = $cfg->getDefaultSLAId();
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['id'],$ids))
                    $sel=true;

                $default = '';
                if ($row['id'] == $defaultId)
                    $default = '<small><em>(Default)</em></small>';
                ?>
            <tr id="<?php echo $row['id']; ?>">
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['id']; ?>"
                    <?php echo $sel?'checked="checked"':''; ?>>
                </td>
                <td>&nbsp;<a href="slas.php?id=<?php echo $row['id'];
                    ?>"><?php echo Format::htmlchars($row['name']);
                    ?></a>&nbsp;<?php echo $default; ?></td>
                <td><?php echo $row['isactive']?__('Active'):'<b>'.__('Disabled').'</b>'; ?></td>
                <td style="text-align:right;padding-right:35px;"><?php echo $row['grace_period']; ?>&nbsp;</td>
                <td>&nbsp;<?php echo Format::db_date($row['created']); ?></td>
                <td>&nbsp;<?php echo Format::db_datetime($row['updated']); ?></td>
            </tr>
            <?php
            } //end of while.
        endif; ?>
    <tfoot>
     <tr>
        <td colspan="6">
            <?php if($res && $num){ ?>
            <?php echo __('Select');?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('All');?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('None');?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('Toggle');?></a>&nbsp;&nbsp;
            <?php }else{
                echo __('No SLA plans found' /* SLA is abbreviation for Service Level Agreement */);
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
</div>
<?php
if($res && $num): //Show options..
    echo '<div class="navigation">';
        echo '<ul class="pagination">';
            echo $pageNav->getPageLinks();
        echo '</ul>';
    echo '</div>';
?>
<p class="centered" id="actions">
    <input class="button btn input-button-primary" style="color: #ffffff" type="submit" name="enable" value="<?php echo __('Enable');?>" >
    <input class="button btn input-button-warning" style="color: #ffffff" type="submit" name="disable" value="<?php echo __('Disable');?>" >
    <input class="button btn input-button-danger" style="color: #ffffff" type="submit" name="delete" value="<?php echo __('Delete');?>" >
</p>
<?php
endif;
?>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>enable</b> %s?'),
            _N('selected SLA plan', 'selected SLA plans', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
            _N('selected SLA plan', 'selected SLA plans', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
            _N('selected SLA plan', 'selected SLA plans', 2)); ?></strong></font>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" id="close-modal" value="<?php echo __('No, Cancel');?>" class="btn btn-danger">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('Yes, Do it!');?>" class="confirm btn btn-primary">
        </span>
     </p>
    <div class="clear"></div>
</div>

<style>

    .dialog{
        margin-top: 20px !important;
    }

    .input-button{
        padding: 6px 12px !important;
        border-radius: 6px;
        font-size: 14px;
        opacity: 1;
    }

    a.input-button:hover{
        text-decoration: none;
        color:#fff;
    }

    .input-button-primary {
        color: #fff;
        background-color: #337ab7;
        border:solid 1px #2e6da4;
    }

    .input-button-default {
        color: #333;
        background-color: #fff;
        border:solid 1px #ccc;
    }

    .input-button-danger{
        color: #fff;
        background-color: #d9534f;
        border:solid 1px #d43f3a;
    }

    .input-button-warning{
        color: #fff;
        background-color: #f0ad4e;
        border:solid 1px #e38d13;
    }

    @media screen and (max-width: 450px) {

        .flush-right{
            width: 100%;
        }

        .flush-right a{
            width: 100%;
            text-align: center;
        }

        a.input-button{
            margin-bottom: 20px !important;
        }

        input[type=submit], input[type=reset], input[type=button]{
            width: 100% !important;
            margin-bottom: 10px !important;
        }

        .dialog{
            margin-top: 20px !important;
            width: 95% !important;
        }

        .navigation{
            text-align: center !important;
        }

    }


</style>