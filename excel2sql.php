<?php
require_once 'Classes/PHPExcel/IOFactory.php';
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
$file = './8.xls';
//$html = '8.html';//excel2html($file);
$html = excel2html($file);
//echo $html."\n";die;
parse($html);
//int,varchar,tinyint,date,datetime,timestamp,text,smallint,mediumint,bigint,float,double,decimal,char,tinytext,mediumtext,longtext,date,time,year


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

function parse($file='') {
    global $default;
    $default_column = 6;
    $result = '';
    if($file) {
        $content = file_get_contents($file);
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
            //print_r($table);die;               
            if($table && isset($table[0]) && isset($table[1]) && isset($table[2])) {
                //echo json_encode($table);
                $table_name = $table[1];
                $table_engine = $table[2];
                $table_comment = $table[3];
                $table = $table[0];
                if($table) {
                    $content = '';
                    foreach($table as $k => $v) {
                        $start = "DROP TABLE IF EXISTS `$table_name[$k]`;\n";
                        $start .= "CREATE TABLE IF NOT EXISTS `$table_name[$k]` (\n"; 
                        $end = ") ENGINE=$table_engine[$k]  DEFAULT CHARSET=utf8 COMMENT='$table_comment[$k]' ;";                    
                        $fields = array();       
                        $v = str_replace('&nbsp;', '', $v);
                        //echo $v;die;                 
                        $field_preg = '~<tr class="row\d+">
			<td class="column0.*">([^字表].*?)</td>
			<td class="column1.*">(.*?)</td>
			<td class="column2.*">(.*?)</td>
			<td class="column3.*">(.*?)</td>
			<td class="column4.*">(.*?)</td>
			<td class="column5.*">(.*?)</td>
		  </tr>~';
                        preg_match_all($field_preg, $v, $fields);
                        //print_r($fields);die;
                        if(!empty($fields)) {
                            $count = count($fields[0]);
                            $primary = 0;
                            for($i=0; $i<$count; $i++) {                                
                                $column_name = $fields[1][$i];
                                $column_type = $fields[2][$i];
                                $column_len = $fields[3][$i];
                                $column_default = $fields[4][$i];
                                $column_comment = $fields[5][$i];
                                $column_primary = $fields[6][$i];
                                $column_default = $column_default ? $column_default : !empty($default[$column_type]) ? $default[$column_type] : ' ';
                                $start .= "`$column_name` $column_type ($column_len) NOT NULL ";
                                if($column_primary == 'Y') {
                                    $start .= 'AUTO_INCREMENT ';
                                    $primary = $i;
                                } else {
                                    if($column_default != ' ') {
                                        $start .= " DEFAULT '".$column_default."' ";
                                    }
                                }
                                $start .= " COMMENT '".$column_comment."',\n";
                            }
                            $start .= "PRIMARY KEY (`".$fields[1][$primary]."`)\n";
                            $content .= $start. $end."\n\n";
                            //echo $content."\n"; 
                        }
                    }
                }
                echo $content;die;
                
            }
        }
    }
}