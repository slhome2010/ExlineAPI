<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-exline" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-exline" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-tax-class"><?php echo $entry_tax_class; ?></label>
                        <div class="col-sm-10">
                            <select name="exline_tax_class_id" id="input-tax-class" class="form-control">
                                <option value="0"><?php echo $text_none; ?></option>
                                <?php foreach ($tax_classes as $tax_class) { ?>
                                    <?php if ($tax_class['tax_class_id'] == $exline_tax_class_id) { ?>
                                        <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
                        <div class="col-sm-10">
                            <select name="exline_geo_zone_id" id="input-geo-zone" class="form-control">
                                <option value="0"><?php echo $text_all_zones; ?></option>
                                <?php foreach ($geo_zones as $geo_zone) { ?>
                                    <?php if ($geo_zone['geo_zone_id'] == $exline_geo_zone_id) { ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                        <div class="col-sm-10">
                            <select name="exline_status" id="input-status" class="form-control">
                                <?php if ($exline_status) { ?>
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                    <option value="1"><?php echo $text_enabled; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="exline_sort_order" value="<?php echo $exline_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
                        </div>
                    </div>
					<div class="form-group">
                        <label class="col-sm-2 control-label" for="input-insurance"><?php echo $entry_insurance; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="exline_insurance" value="<?php echo $exline_insurance; ?>" placeholder="<?php echo $entry_insurance; ?>" id="input-insurance" class="form-control" />
                        </div>
                    </div>					
					<div class="form-group">
                        <label class="col-sm-2 control-label" for="input-percent"><?php echo $entry_percent; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="exline_percent" value="<?php echo $exline_percent; ?>" placeholder="<?php echo $entry_percent; ?>" id="input-percent" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-origin"><span data-toggle="tooltip" title="<?php echo $help_origin; ?>"><?php echo $entry_origin; ?></span></label>
                        <div class="col-sm-10">
                            <input type="text" name="exline_origin_city" value="<?php echo $exline_origin_city; ?>" placeholder="<?php echo $entry_origin; ?>" id="input-origin" class="form-control" />
                            <input type="hidden" name="exline_origin_id" value="<?php echo $exline_origin_id; ?>" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
    .dropdown-menu {
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 20px;
        font-size: 12px;
    }
</style>
<script type="text/javascript"><!--
var extension = '<?php echo $extension; ?>';

$('input[name=\'exline_origin_city\']').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: 'index.php?route='+extension+'shipping/exline/autocomplete&token=<?php echo $token; ?>&iso_code_2=<?php echo $iso_code_2; ?>',
                dataType: 'json',
                success: function (json) {
                    response($.map(json, function (item) {
                        return {
                            label: item['title'],
                            value: item['id']
                        };
                    }));
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert('<?php echo $error_origin_country; ?>');
                }
            });
        },
        select: function (item) {
            $('input[name=\'exline_origin_city\']').val(item['label']);
            $('input[name=\'exline_origin_id\']').val(item['value']);
        }
    });
//--></script>
<?php echo $footer; ?>