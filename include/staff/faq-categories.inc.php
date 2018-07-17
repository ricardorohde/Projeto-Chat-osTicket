<?php
if(!defined('OSTSTAFFINC') || !$thisstaff) die('Access Denied');

?>
<h2><?php echo __('Frequently Asked Questions');?></h2>
<form id="kbSearch" action="kb.php" method="get">
    <input type="hidden" name="a" value="search">
    <div>
        <input id="query" type="text" class="form-control" name="q" value="<?php echo Format::htmlchars($_REQUEST['q']); ?>" style="width:25%;">
        <select name="cid" id="cid" class="form-control" style="width:25%;">
            <option value="">&mdash; <?php echo __('All Categories');?> &mdash;</option>
            <?php
            $sql='SELECT category_id, name, count(faq.category_id) as faqs '
                .' FROM '.FAQ_CATEGORY_TABLE.' cat '
                .' LEFT JOIN '.FAQ_TABLE.' faq USING(category_id) '
                .' GROUP BY cat.category_id '
                .' HAVING faqs>0 '
                .' ORDER BY cat.name DESC ';
            if(($res=db_query($sql)) && db_num_rows($res)) {
                while($row=db_fetch_array($res))
                    echo sprintf('<option value="%d" %s>%s (%d)</option>',
                        $row['category_id'],
                        ($_REQUEST['cid'] && $row['category_id']==$_REQUEST['cid']?'selected="selected"':''),
                        $row['name'],
                        $row['faqs']);
            }
            ?>
        </select>
        <input id="searchSubmit" type="submit" class="btn btn-primary" value="<?php echo __('Search');?>">
        <select name="topicId" style="width:25%; margin-top: 3px;" id="topic-id" class="form-control">
            <option value="">&mdash; <?php echo __('All Help Topics');?> &mdash;</option>
            <?php
            $sql='SELECT ht.topic_id, CONCAT_WS(" / ", pht.topic, ht.topic) as helptopic, count(faq.topic_id) as faqs '
                .' FROM '.TOPIC_TABLE.' ht '
                .' LEFT JOIN '.TOPIC_TABLE.' pht ON (pht.topic_id=ht.topic_pid) '
                .' LEFT JOIN '.FAQ_TOPIC_TABLE.' faq ON(faq.topic_id=ht.topic_id) '
                .' GROUP BY ht.topic_id '
                .' HAVING faqs>0 '
                .' ORDER BY helptopic';
            if(($res=db_query($sql)) && db_num_rows($res)) {
                while($row=db_fetch_array($res))
                    echo sprintf('<option value="%d" %s>%s (%d)</option>',
                        $row['topic_id'],
                        ($_REQUEST['topicId'] && $row['topic_id']==$_REQUEST['topicId']?'selected="selected"':''),
                        $row['helptopic'], $row['faqs']);
            }
            ?>
        </select>
    </div>
</form>
<hr>
<div>
<?php
if($_REQUEST['q'] || $_REQUEST['cid'] || $_REQUEST['topicId']) { //Search.
    $sql='SELECT faq.faq_id, question, ispublished, count(attach.file_id) as attachments, count(ft.topic_id) as topics '
        .' FROM '.FAQ_TABLE.' faq '
        .' LEFT JOIN '.FAQ_CATEGORY_TABLE.' cat ON(cat.category_id=faq.category_id) '
        .' LEFT JOIN '.FAQ_TOPIC_TABLE.' ft ON(ft.faq_id=faq.faq_id) '
        .' LEFT JOIN '.ATTACHMENT_TABLE.' attach
             ON(attach.object_id=faq.faq_id AND attach.type=\'F\' AND attach.inline = 0) '
        .' WHERE 1 ';

    if($_REQUEST['cid'])
        $sql.=' AND faq.category_id='.db_input($_REQUEST['cid']);

    if($_REQUEST['topicId'])
        $sql.=' AND ft.topic_id='.db_input($_REQUEST['topicId']);

    if($_REQUEST['q']) {
        $sql.=" AND (question LIKE ('%".db_input($_REQUEST['q'],false)."%')
                 OR answer LIKE ('%".db_input($_REQUEST['q'],false)."%')
                 OR keywords LIKE ('%".db_input($_REQUEST['q'],false)."%')
                 OR cat.name LIKE ('%".db_input($_REQUEST['q'],false)."%')
                 OR cat.description LIKE ('%".db_input($_REQUEST['q'],false)."%')
                 )";
    }

    $sql.=' GROUP BY faq.faq_id ORDER BY question';

    echo "<div><strong>".__('Search Results')."</strong></div><div class='clear'></div>";
    if(($res=db_query($sql)) && db_num_rows($res)) {
        echo '<div id="faq">
                <ol>';
        while($row=db_fetch_array($res)) {
            echo sprintf('
                <li><a href="faq.php?id=%d" class="previewfaq">%s</a> - <span>%s</span></li>',
                $row['faq_id'],$row['question'],$row['ispublished']?__('Published'):__('Internal'));
        }
        echo '  </ol>
             </div>';
    } else {
        echo '<strong class="faded">'.__('The search did not match any FAQs.').'</strong>';
    }
} else { //Category Listing.
    $sql='SELECT cat.category_id, cat.name, cat.description, cat.ispublic, count(faq.faq_id) as faqs '
        .' FROM '.FAQ_CATEGORY_TABLE.' cat '
        .' LEFT JOIN '.FAQ_TABLE.' faq ON(faq.category_id=cat.category_id) '
        .' GROUP BY cat.category_id '
        .' ORDER BY cat.name';
    if(($res=db_query($sql)) && db_num_rows($res)) {
        echo '<div>'.__('Click on the category to browse FAQs or manage its existing FAQs.').'</div>
                <ul id="kb">';
        while($row=db_fetch_array($res)) {

            echo sprintf('
                <li>
                    <h4><a href="kb.php?cid=%d">%s (%d)</a> - <span>%s</span></h4>
                    %s
                </li>',$row['category_id'],$row['name'],$row['faqs'],
                ($row['ispublic']?__('Public'):__('Internal')),
                Format::safe_html($row['description']));
        }
        echo '</ul>';
    } else {
        echo __('NO FAQs found');
    }
}
?>
</div>

<style>

    input, select{
        margin-right: 10px !important;
    }

    .modal-content{
        height: 550px !important;
        overflow: scroll !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }

    @media screen and (max-width: 450px) {

        #pjax-container {
            width: 100% !important;
        }

        input, select {
            width: 100% !important;
            margin-bottom: 10px !important;
        }

        input[type=submit], input[type=reset], input[type=button] {
            margin-bottom: 10px;
        }

        .navbar {
            z-index: 2 !important;
        }

        .redactor_box {
            z-index: 1 !important;
        }

    }

</style>
