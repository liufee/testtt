<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2016-10-19 14:40
 */
?>
<section class="section">
    <div class="step">
        <ul class="unstyled">
            <li class="on"><em>1</em>检测环境</li>
            <li class="on"><em>2</em>创建数据</li>
            <li class="current"><em>3</em>完成安装</li>
        </ul>
    </div>
    <div class="install" id="log">
        <ul id="loginner" class="unstyled"></ul>
    </div>
    <div class="bottom text-center">
        <a href="javascript:;"><i class="fa fa-refresh fa-spin"></i>&nbsp;正在安装...</a>
    </div>
</section>
<script type="text/javascript">
    function showmsg(content, status) {
        var icon = '<i class="fa fa-check correct"></i> ';
        if (status == "error") {
            icon = '<i class="fa fa-remove error"></i> ';
        }
        $('#loginner').append("<li>" + icon + content + "</li>");
        $("#log").scrollTop(1000000000);
    }
</script>
