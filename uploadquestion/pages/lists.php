<hr/>
<?php if($existing_sample_data):?>
    <table class="table table-striped generaltable" id="table_latin_questionupload">
        <thead>
        <tr>
            <th width="350">Latin</th>
            <th>Translation</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($existing_sample_data as $rs):?>
            <tr>
                <td><?php echo $rs->latin;?></td>
                <td class="small">
                    <?php for($i=1; $i<=11; $i++):?>
                        <?php echo (!empty($rs->{"translation_$i"})) ? $rs->{"translation_$i"}.'<br/>' : '';?>
                    <?php endfor;?>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
<?php else:?>
    <div class="block">
        <div class="text-xs-center text-center mt-3" data-region="empty-message">
            <img class="empty-placeholder-image-lg mt-1" src="/admin/tool/uploadquestion/assets/images/empty.svg" alt="<?php echo get_string('norecentdata','tool_uploadquestion');?>" role="presentation">
            <p class="text-muted mt-3"><?php echo get_string('norecentdata','tool_uploadquestion');?></p>
        </div>
    </div>
<?php endif;?>



