<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>分分钟印|杭电校园照片快印服务|在线冲印|证件照快印</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Plusman">
        <meta name="email" content="plusmancn@gmail.com">
        <meta name="QQ" content="838070635">
        <!-- Bootstrap -->
        <link href="<?=$base_url?>static/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen,print">

        <script src="//code.jquery.com/jquery.js"></script>

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="../../assets/js/html5shiv.js"></script>
          <script src="../../assets/js/respond.min.js"></script>
        <![endif]-->

    </head>

    <body>

        <div class="container">
          <? foreach ($listAll as $key1 => $user):?>
            <div class="alert alert-info">
                <?  
                    $description = '';
                    foreach ($user as $key2 => $value2) {
                        if($key2 == 'name'){
                            $description .= '【姓名:'.$value2.'】';
                        }else{    
                            $description .= '【'.$key2.':'.$value2.'元】';
                        }
                    }

                    echo $description;
                ?>

            </div>
          <? endforeach;?>
        </div>
		

		<!-- 加载驱动Javascript -->
        <script src="<?=$base_url?>static/bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>