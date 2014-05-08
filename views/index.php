<? echo form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=store_bundles'); ?>

<?php
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('preference'), 'style' => 'width:50%;'),
    lang('setting')
);

foreach ($settings as $key => $val)
{
    $this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

?>

<p><? echo form_submit('submit', lang('submit'), 'class="submit"'); ?></p>
<?php $this->table->clear()?>
<? echo form_close()?>
<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/link_truncator/views/index.php */