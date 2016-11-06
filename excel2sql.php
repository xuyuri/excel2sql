<?php
require_once 'resource/Classes/PHPExcel/IOFactory.php';
//数据类型默认值
$default = array(
    'int' => '0',
    'varchar' => '',
    'tinyint' => '0',
    'text' => '',
    'smallint' => '0',
    'mediumint' => '0',
    'bigint' => '0',
    'float' => '0.0',
    'double' => '0.0',
    'decimal' => '0.0',
    'char' => '',
    'tinytext' => '',
    'mediumtext' => '',
    'longtext' => '',
);
//数据表默认字段列表
$default_sql = "    `create_time` datetime NOT NULL COMMENT '创建时间',
    `creator_wechat` varchar(50) NOT NULL DEFAULT '' COMMENT '创建者用户微信号',
    `operate_time` datetime NOT NULL COMMENT '最后操作时间',
    `operator_wechat` varchar(50) NOT NULL DEFAULT '' COMMENT '操作者用户微信',
    `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录IP',
    ";
//整型字段列表
$integer = ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'];
//转换结果存储文件
$result_file = 'result.txt';
$tmp_url = '';          //上传文件临时地址
$html_url = '';         //转换完成的html文件地址
$content = '';          //生成的sql语句集合

main();

/**
 * Excel => Sql入口函数
 * @author             yurixu 2016/11/2
 */
function main() {
    global $tmp_url, $content, $result_file;
    //上传文件检测
    $init = initFile();
    if($init) {
        //Excel文件转Html
        $html_url = excel2html($tmp_url);
        if(!empty($html_url)) {
            //Html文件解析
            $content = html2sql($html_url);
            @unlink($html_url);
            if (!empty($content)) {
                @file_put_contents($result_file, $content);
                echo "success";
            }
        }
    }
}

/**
 * 上传文件检测
 * @return bool
 * @author             yurixu 2016/11/2
 */
function initFile() {
    $result = false;
    global $tmp_url, $content;
    if ($_FILES["file"]["error"] > 0 ) {
        $content = "Error: " . $_FILES["file"]["error"];
    } else {
        //文件类型：$_FILES["file"]["type"] 文件大小：$_FILES["file"]["size"] 文件名：$_FILES["file"]["name"] 文件存储位置：$_FILES["file"]["tmp_name"]
        $tmp_url = $_FILES["file"]["tmp_name"];
        $result = true;
    }
    return $result;
}

/**
 * Excel文件转换为Html文件
 * @param string $file      Excel文件地址
 * @return string           转换为Html的文件地址
 * @throws PHPExcel_Reader_Exception
 * @throws PHPExcel_Writer_Exception
 * @author                  yurixu 2016/11/2
 */
function excel2html($file='') {
    $result = '';
    if($file) {
        $path_info = pathinfo($file);
        if($path_info && isset($path_info['filename'])) {
            $file_name = $path_info['filename'].'.html';
            $phpExcel = new PHPExcel;
            $excelReader = PHPExcel_IOFactory::createReader('Excel5');
            $excelReader->setReadDataOnly(true);
            $phpExcel = $excelReader->load($file);
            $objWriteHTML = new PHPExcel_Writer_HTML($phpExcel);
            $objWriteHTML->save($file_name);
            $result = $file_name;
        }
    }
    return $result;
}

/**
 * 解析Html文件内容，生成SQL语句
 * @param string $file  html文件地址
 * @return string       生成的sql内容字符串
 * @author              yurixu 2016/11/2
 */
function html2sql($file='') {
    global $default, $integer, $default_sql;
    $result = '';
    if(!empty($file)) {
        $content = @file_get_contents($file);
        if($content) {
            $table_preg = '~<tr class="row\d+">
			<td class="column0.*">表名</td>
			<td class="column1.*">(.*?)</td>
			<td class="column2.*">引擎类型</td>
			<td class="column3.*">(.*?)</td>
			<td class="column4.*">备注</td>
			<td class="column5.*">(.*?)</td>
		  </tr>
		  <tr class="row\d+">
			<td class="column0.*">字段名称</td>
			<td class="column1.*">字段类型</td>
			<td class="column2.*">字段长度</td>
			<td class="column3.*">默认值</td>
			<td class="column4.*">备注</td>
			<td class="column5.*">是否主键</td>
		  </tr>
		  (<tr class="row\d+">
			<td class="column0.*">[^表].*</td>
			<td class="column1.*">.*</td>
			<td class="column2.*">.*</td>
			<td class="column3.*">.*</td>
			<td class="column4.*">.*</td>
			<td class="column5.*">.*</td>
		  </tr>\s*)*~';
            preg_match_all($table_preg, $content, $table);
            if($table && isset($table[0]) && isset($table[1]) && isset($table[2])) {
                $table_name = $table[1];        //表名
                $table_engine = $table[2];      //引擎类型
                $table_comment = $table[3];     //表备注
                $table = $table[0];
                if($table) {
                    $result = '';
                    foreach($table as $k => $v) {
                        $start = "DROP TABLE IF EXISTS `$table_name[$k]`;\n";
                        $start .= "CREATE TABLE IF NOT EXISTS `$table_name[$k]` (\n"; 
                        $end = ") ENGINE = $table_engine[$k] DEFAULT CHARSET=utf8 COMMENT='$table_comment[$k]' ;";                    
                        $fields = array();       
                        $v = str_replace('&nbsp;', '', $v);
                        $field_preg = '~<tr class="row\d+">
			<td class="column0.*">([^字表].*?)</td>
			<td class="column1.*">(.*?)</td>
			<td class="column2.*">(.*?)</td>
			<td class="column3.*">(.*?)</td>
			<td class="column4.*">(.*?)</td>
			<td class="column5.*">(.*?)</td>
		  </tr>~';
                        preg_match_all($field_preg, $v, $fields);
                        if(!empty($fields)) {
                            $count = count($fields[0]);
                            $primary = 0;
                            for($i=0; $i<$count; $i++) {                                
                                $column_name = $fields[1][$i];          //字段名
                                $column_type = $fields[2][$i];          //字段类型
                                $column_len = $fields[3][$i];           //字段长度
                                $column_default = $fields[4][$i];       //字段默认值
                                $column_comment = $fields[5][$i];       //字段备注
                                $column_primary = $fields[6][$i];       //字段主键
                                $column_default = $column_default ? $column_default : !empty($default[$column_type]) ? $default[$column_type] : ' ';
                                $start .= "    `$column_name` $column_type ($column_len) ";
                                //整型自动设置为无符号类型unsigned
                                if(in_array($column_type, $integer)) {
                                    $start .= 'unsigned ';
                                }
                                $start .= 'NOT NULL ';
                                //主键自动设置为自增
                                if($column_primary == 'Y') {
                                    $start .= 'AUTO_INCREMENT ';
                                    $primary = $i;
                                } else {
                                    if($column_default != ' ') {
                                        $start .= "DEFAULT '".$column_default."' ";
                                    }
                                }
                                $start .= "COMMENT '".$column_comment."',\n";
                            }
                            $start .= $default_sql;
                            $start .= " PRIMARY KEY (`".$fields[1][$primary]."`)\n";
                            $result .= $start. $end."\n\n";
                        }
                    }
                }
            }
        }
    }
    return $result;
}
