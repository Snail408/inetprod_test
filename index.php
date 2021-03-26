<?php



function draw(array $arr=[], $comment=false, $divcss=false){
    echo "<div style='{$divcss}'>";
    if($comment)echo "$comment<br>";
    if(count($arr))echo '<pre>'.print_r($arr, 1).'</pre>';
    echo '<br></div>';
}

draw([], '<div style="position:fixed; bottom:1em; right:1em; border:1px solid gray; padding:0.5em; background: rgba(123,123,123,0.25);">Gen at: '.date('Y.m.d - H:i:s').'</div>');

$in = [
  ['id' => 1, 'date' => "12.01.2020", 'name' => "test1"],
  ['id' => 2, 'date' => "02.05.2020", 'name' => "test2"],
  ['id' => 4, 'date' => "08.03.2020", 'name' => "test4"],
  ['id' => 1, 'date' => "22.01.2020", 'name' => "test1"],
  ['id' => 2, 'date' => "11.11.2020", 'name' => "test4"],
  ['id' => 3, 'date' => "06.06.2020", 'name' => "test3"],
];
draw($in, '<h1>Входной массив</h1>');
draw([], '<hr />');


////////////////////// Q#1 //////////////////////
draw([], '<h2>1. Уникализация массива по id субмассивов</h2>');

// >>>>>>>>>>>>>>>>>>>> CODE >>>>>>>>>>>>>>>>>>>> //
$out=array_filter($in, function($line){
    static $ids=[];
    if(!in_array($line['id'], $ids)){
        $ids[]=$line['id'];
        return true;
    }else return false;
});
// <<<<<<<<<<<<<<<<<<<<< CODE <<<<<<<<<<<<<<<<<<<<< //

draw($out, '<u>Результат</u>');
draw([], '<hr />');


////////////////////// Q#2 //////////////////////
draw([], '<h2>2. Сортировка массива по id субмассивов</h2>');

// >>>>>>>>>>>>>>>>>>>> CODE >>>>>>>>>>>>>>>>>>>> //
$out=$in;
usort($out, function ($a, $b) {
    if($a['id']==$b['id'])return strtotime($b['date'])-strtotime($a['date']); // + обратная сортировка по дате:)
    return $a['id'] - $b['id'];
});
// Альтернатива:
//array_multisort(array_column($out, 'id'), SORT_ASC, $out);
// <<<<<<<<<<<<<<<<<<<<< CODE <<<<<<<<<<<<<<<<<<<<< //

draw($out, '<u>Результат</u>');
draw([], '<hr />');
$in_reform=$out;


////////////////////// Q#3 //////////////////////
draw([], '<h2>3. Получаем массивы только с id субмассивов = 2</h2>');

// >>>>>>>>>>>>>>>>>>>> CODE >>>>>>>>>>>>>>>>>>>> //
$out=array_filter($in, function($line){return $line['id']==2;});
// <<<<<<<<<<<<<<<<<<<<< CODE <<<<<<<<<<<<<<<<<<<<< //

draw($out, '<u>Результат</u>');
draw([], '<hr />');


////////////////////// Q#4 //////////////////////
draw([], '<h2>4. Переформирование исходного многомерного массива</h2>');

// >>>>>>>>>>>>>>>>>>>> CODE >>>>>>>>>>>>>>>>>>>> //
$out=[];
array_walk ($in_reform, function($v) use(&$out){
    $out[$v['name']]=$v['id'];
});
// <<<<<<<<<<<<<<<<<<<<< CODE <<<<<<<<<<<<<<<<<<<<< //

draw([], 'Для получения четкого соответствия по задаче (т.е. последовательности ) можно работать с исходным массивом, но проверять использовался ли ключ уже. Можно и по-другому крутить массивчик:)');
draw($out, '<u>Результат</u>');
draw([], '<hr />');


////////////////////// Q#5 //////////////////////
draw([], '<h2>5. Запрос к БД #1</h2>');

include_once 'sqlite.php';
$dbLocation=':memory:';
//$dbLocation='test.db';
try{
    $dbLite=new DBlite($dbLocation);
    $dbLite->prepare(5); // Create and fill tables
    $db=$dbLite->db;
}catch (Exception $e){
    $db=false;
    draw([], '<h3><i>--- '.$e->getMessage().'--- </i> in file '.$e->getFile().' on line '.$e->getLine().'</h3>');
}

// >>>>>>>>>>>>>>>>>>>> CODE >>>>>>>>>>>>>>>>>>>> //
$query='SELECT goods.*, GROUP_CONCAT(goods_tags.tag_id) as used_tags FROM goods LEFT JOIN goods_tags ON goods.id=goods_tags.goods_id WHERE goods_tags.goods_id IS NOT NULL GROUP BY goods.id ORDER BY COUNT(goods_tags.tag_id) DESC';
// <<<<<<<<<<<<<<<<<<<<< CODE <<<<<<<<<<<<<<<<<<<<< //

draw([], 'В силу того что задача стоит не однозначно, а также входные данные можно понимать по-разному, сделал выборку всех товаров которые имеют хотя бы один тег и отсортировал по упоминаемости по убаванию.');
draw([], '<u>Запрос</u><br><b>'.$query.'</b>');

if($db){
    $boxCss='box-sizing:border-box;width:25%;float:right;overflow:auto;max-height:1000px;border:1px solid lightgray;padding:1em;';

// COMMON result // show
    $data=$db->query($query);

    $result=[];
    while ($d=$data->fetchArray(SQLITE3_ASSOC)){
        $result[]=$d;
    }

    draw($result, '<u>Результат ('.count($result).' goods)</u>', $boxCss);

// GOODS-2-TAGS // show
    $data=$db->query('SELECT * FROM goods_tags');
    $result=[];
    while ($d=$data->fetchArray(SQLITE3_ASSOC)){
        $result[]=$d;
    }
    draw($result, '<u>GOODS-2-TAGS</u>', $boxCss);

// GOODS // show
    $data=$db->query('SELECT * FROM goods');
    $result=[];
    while ($d=$data->fetchArray(SQLITE3_ASSOC)){
        $result[]=$d;
    }
    draw($result, '<u>TAGS</u>', $boxCss);

// TAGS // show
    $data=$db->query('SELECT * FROM tags');
    $result=[];
    while ($d=$data->fetchArray(SQLITE3_ASSOC)){
        $result[]=$d;
    }
    draw($result, '<u>GOODS</u>', $boxCss);
}

draw([], '<hr />', 'clear:both;');

////////////////////// Q#6 //////////////////////
draw([], '<h2>6. Запрос к БД #2</h2>');

// >>>>>>>>>>>>>>>>>>>> CODE >>>>>>>>>>>>>>>>>>>> //
$query='SELECT DISTINCT department_id FROM evaluations WHERE gender = 1 AND value > 5 GROUP BY value';
// <<<<<<<<<<<<<<<<<<<<< CODE <<<<<<<<<<<<<<<<<<<<< //

draw([], 'Не понятно для чего и почему department_id - uuid, по-этому в тесте использовал просто int.');
draw([], '<u>Запрос</u><br><b>'.$query.'</b>');

if($db){
    $boxCss='box-sizing:border-box;width:50%;float:right;overflow:auto;max-height:400px;border:1px solid lightgray;padding:1em;';

    try {
        $dbLite->prepare(6); // Create and fill tables
    }catch (Exception $e){
        draw([], '<h3><i>--- '.$e->getMessage().'--- </i> in file '.$e->getFile().' on line '.$e->getLine().'</h3>');
    }

// COMMON result // show
    $data=$db->query($query);
    $result=[];
    while ($d=$data->fetchArray(SQLITE3_ASSOC)){
        $result[]=$d;
    }
    draw($result, '<u>Результат</u>', $boxCss);

// EVALUTIONS // show
    $data=$db->query('SELECT * FROM evaluations');
    $result=[];
    while ($d=$data->fetchArray(SQLITE3_ASSOC)){
        $result[]=$d;
    }
    draw($result, '<u>EVALUTIONS</u>', $boxCss);
}

draw([], '<hr />', 'clear:both;');