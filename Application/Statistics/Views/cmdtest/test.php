<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <title>TestClient-调试工具</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="/assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/assets/img/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/assets/img/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/assets/img/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="/assets/img/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="/assets/img/favicon.png">

    <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/assets/js/scripts.js"></script>
    <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="/assets/js/highcharts.js"></script>
</head>
<body>

<div class="container">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a id="testPage">调试界面</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="row clearfix">

        <!-- 表单区start -->
        <div class="col-md-12 column">
            <form method="post" action="/cmdtest.php">
                <div class="col-md-12 column list_item">
                    <span>选择服务项目</span>
                    <select name="rpc_name" id="rpc_name">
                        <?php if(isset($serviceList) && !empty($serviceList)): ?>
                            <?php foreach($serviceList as $row): ?>
                                <option value="<?php echo $row; ?>" <?php if(isset($_POST['rpc_name']) && $_POST['rpc_name'] == $row){ echo 'selected';}?> ><?php echo $row; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <br/>
                </div>

                <div class="col-md-12 column list_item">
                    <span class="list_title">类名</span>
                    <input name="class_name" id="class_name" value="<?php if(isset($_POST['class_name'])){echo $_POST['class_name'];}?>" autocomplete="off" disableautocomplete/> &nbsp;&nbsp;&nbsp;&nbsp;
                    <span id="search_class">搜索</span>
                    <div id="class_list"> </div>
                </div>

                <div class="col-md-12 column list_item">
                    <span class="list_title">方法名</span>
                    <input name="function_name" id="function_name" value="<?php if(isset($_POST['function_name'])){echo $_POST['function_name'];}?>" autocomplete="off" disableautocomplete /> &nbsp;&nbsp;&nbsp;&nbsp;
                    <span id="search_function">搜索</span>
                    <div id="function_list"> </div>
                </div>

                <div class="col-md-12 column list_item" id="argv_list">
                    <div class="list_item">
                        <span class="list_title">参数</span>
                        <input name="argv[]" class="argv_list" value="<?php if(isset($_POST['argv'][0])){ echo $_POST['argv'][0]; }?>"/> <span class="drop_argv_button">【-】</span>
                    </div>

                    <?php if($_POST && isset($_POST['argv']) && count($_POST['argv']) > 1) : ?>
                        <?php foreach($_POST['argv'] as $argv_key => $argv_val): ?>
                            <?php if($argv_key == 0){ continue; } ?>
                            <div class="list_item">
                                <span class="list_title">参数</span>
                                <input name="argv[]" class="argv_list" value="<?php echo $argv_val; ?>" init_val="<?php echo $argv_val; ?>"/> <span class="drop_argv_button">【-】</span>
                            </div>
                        <?php endforeach;?>
                    <?php endif;?>
                </div>

                <div class="col-md-12 column list_item" style="margin-top:15px;">
                    <span id="add_argv_button">【+】点击添加参数</span>
                </div>

                <div class="col-md-12 column list_item" style="margin-top:20px;">
                    <input type="submit" value="提交" />
                </div>
            </form>
        </div>
        <!-- 表单区end -->

        <!-- 结果输出区start -->
        <div class="col-md-12 column" id="service_content" style="margin-top:10px;">
            <?php if(isset($service_data)){ echo '<h3>返回结果</h3><pre>'; print_r($service_data); echo '</pre><br>程序执行耗时：'. $cost_time .' seconds'; }  ?>
            <!--开始时间: <?php echo $time_start ;?>
			结束时间: <?php echo $time_end ;?> -->
        </div>
        <!-- 结果输出区end -->

    </div>
</div>

<div class="footer">Powered by <a href="http://www.workerman.net" target="_blank"><strong>Workerman!</strong></a></div>
</body>
</html>

<style>
    .list_title {
        display:block;
        width:100px;
        /*border:1px solid red;*/
        float:left;
    }
    .list_item {
        margin:5px 0px 5px 0px;
    }

    #class_list {
        /*position: absolute; */
        width: 476px; display: none;
        border:1px solid green;
        z-index:10000;
    }
    #function_list {
        /*position: absolute; */
        width: 476px; display: none;
        border:1px solid green;
        z-index:10000;
    }

    #class_list ol , #function_list ol {
        list-style: none;
        padding-left:0px;
        margin:0px;
    }

    #class_list li , #function_list li {
        /*border:1px solid green;
        box-sizing:border-box; */
        padding-left:1px;
    }
</style>

<script>
    $(document).ready(function(){

        //点击取消列表显示
        $(document).delegate(".hide_button","click",function(){
            $(this).parent().hide();
        });

        //绑定添加参数事件
        $('#add_argv_button').click(function(){
            $('#argv_list').append('<div class="list_item"><span class="list_title">参数</span> <input name="argv[]" /> <span class="drop_argv_button">【-】</span></div>');
        })

        // 绑定去掉参数事件
        $(document).on('click', '.drop_argv_button', function() {
            $(this).parent().remove();
        });

        //点击查找类名的按钮事件
        $('#search_class').click(function(){
            getClassFunctions('class');
        });

        //点击查找方法名的按钮事件
        $('#search_function').click(function(){
            getClassFunctions('function');
        });

        $('#class_name').click(function(){
            getClassFunctions('class');
        })
        $('#function_name').click(function(){
            getClassFunctions('function');
        })

        $('#function_name,#class_name').keydown(function () {
            //alert('up');
            $('#function_list').hide();
            $('#class_list').hide();
        });

        //给弹出层绑定点击事件
        $(document).delegate("#class_list li","click",function(){
            var select_name = $(this).html();
            $('#class_name').val(select_name);
            $('#function_name').val('');
            $('#argv_list').empty();
            $(this).parent().parent().hide();
        });

        //首次输入字符，隐藏提示
        $(document).on('click', '.argv_list', function() {
            if($(this).attr('is_click')){
                return true;
            }else{
                $(this).attr('is_click',1);
            }

            if($(this).attr('init_val') && $(this).attr('init_val') == $(this).val()){
                $(this).val('');
            }
            return true;
        });

        //给弹出层绑定点击事件
        $(document).delegate("#function_list li","click",function(){
            var select_name = $(this).html();
            $('#function_name').val(select_name);
            $(this).parent().parent().hide();

            //给方法用到的参数列出来作为提示
            if(window.function_list){
                var argv_list_html = '';
                for(var key in window.function_list){
                    if(key == select_name){
                        for(var argv_key in window.function_list[key]){
                            argv_list_html += '<div class="list_item">';
                            argv_list_html += '<span class="list_title">参数</span> ';
                            argv_list_html += '<input name="argv[]" class="argv_list" value="'+window.function_list[key][argv_key]+'" init_val="'+window.function_list[key][argv_key]+'" title="'+window.function_list[key][argv_key]+'" /> <span class="drop_argv_button">【-】</span>';
                            argv_list_html += '</div>';
                        }

                        if(argv_list_html == ''){
                            argv_list_html += '<div class="list_item"><span class="list_title">参数</span> <input name="argv[]" class="argv_list" value=""> <span class="drop_argv_button">【-】</span></div>';
                        }

                        if(argv_list_html){
                            $('#argv_list').html(argv_list_html);
                        }
                    }
                }
            }

        });

        //控制显示弹出层
        $(document).delegate("#function_list,#class_list","click",function(){
            $(this).hide();
        });

        //获取目前service的类和方法
        function getClassFunctions(action){
            var rpc_name = $('#rpc_name').val();
            $.ajax({
                type: "GET",
                url: "/cmdtest.php",
                data: "action=get_class&rpc_name="+rpc_name,
                dataType: 'json',
                success: function(msg){
                    if(msg){
                        var var_html = '';
                        if(action == 'class'){
                            for(var key in msg){
                                var_html += '<li>'+key+'</li>';
                            }
                            if(var_html){
                                var_html = '<span class="hide_button">点击隐藏列表</span><ol>'+var_html+'</ol>';
                                $('#class_list').html(var_html).show();
                            }
                        }else if(action == 'function'){
                            var var_html = ''
                            var class_name=$('#class_name').val();
                            if(!class_name){
                                alert('请输入类名后再点击搜索！'); return false;
                            }
                            for(var key in msg){
                                if(key == class_name){
                                    window.function_list = msg[key];
                                    for(var function_key in msg[key]) {
                                        var_html += '<li>'+function_key+'</li>';
                                    }
                                }
                            }

                            if(var_html){
                                var_html = '<span class="hide_button">点击隐藏列表</span><ol>'+var_html+'</ol>';
                                $('#function_list').html(var_html).show();
                            }
                        }
                    }
                }
            });
        }

    })
</script>
