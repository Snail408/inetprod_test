<?php

class DBlite{

    public $db;

// Так конечно не правильно - в продакшене должен быть singletone и т.д...
    function __construct($location){
        $vSqlite=SQLite3::version();
        if(!is_array($vSqlite) || !isset($vSqlite['versionString']) || version_compare($vSqlite['versionString'], '3.0.0', '<=')){
            throw new Exception('SQLite is not available or version is lower than 3. SQLite3 is required to view test in action!');
        }

        $this->db = new SQLite3($location);
        $this->db->enableExceptions(true);
        return $this;
    }

    // Just build test tables
    public function prepare($q){
        $reqMethod=__FUNCTION__.'_q'.$q;
        if(method_exists($this, $reqMethod))return call_user_func([$this, $reqMethod]);
        else throw new Exception('Called preparation is not found!');
    }

    private function checkTables($required){
        $tables = $this->db->query("SELECT name FROM sqlite_master WHERE type='table';");
        for($nrows = 0; $table=$tables->fetchArray(SQLITE3_NUM); ++$nrows){
            $i=array_search(reset($table), $required);
            if($i!==false)unset($required[$i]);
        }
        return count($required)<=0;
    }

/*
--- goods ---
| id | name |
| 1 | G1 |
| 2 | G2 |
| 3 | G3 |
| .. | .. |

--- tags ---
| id | name |
| 1 | T1 |
| 2 | T2 |
| 3 | T3 |
| .. | .. |

--- goods_tags  ---
| tag_id | goods_id |
| 1 | 2 |
| 2 | 2 |
| 2 | 1 |
| 3 | 1 |
| 3 | 3 |
| .. | .. |
*/

    private function prepare_q5(){
        if($this->checkTables(['goods', 'tags', 'goods_tags']))return;

        // Create DBs
        $this->db->exec('DROP TABLE IF EXISTS goods');
        $this->db->exec('CREATE TABLE goods (id INTEGER, name TEXT)');
        $this->db->exec('DROP TABLE IF EXISTS tags');
        $this->db->exec('CREATE TABLE tags (id INTEGER, name TEXT)');
        $this->db->exec('DROP TABLE IF EXISTS goods_tags');
        $this->db->exec('CREATE TABLE goods_tags  (tag_id INTEGER, goods_id INTEGER, UNIQUE(tag_id, goods_id))');

        // Fill DBs
        for($i=0; $i<10; $i++){
            $this->db->exec("INSERT INTO goods (id, name) VALUES ({$i}, 'Good-{$i}')");
            $this->db->exec("INSERT INTO tags (id, name) VALUES ({$i}, 'Tag-{$i}')");
        }

        for($i=0; $i<25; $i++) {
            $g=rand(0, 10);
            $t=rand(0, 10);
            try{
                $this->db->exec("INSERT INTO goods_tags (tag_id, goods_id) VALUES ({$g}, {$t})");
            }catch (Exception $e){
                $i--;
            }
        }
    }

    private function prepare_q6(){
        if($this->checkTables(['evaluations']))return;

        // Create DB
        $this->db->exec('CREATE TABLE evaluations (respondent_id TEXT NOT NULL PRIMARY KEY, department_id TEXT NOT NULL, gender INTEGER NOT NULL DEFAULT 0, value INTEGER, UNIQUE(respondent_id))');

        include_once 'uuid.php';
        // Fill DB
        for($i=0; $i<25; $i++) {
            $r=UUID::v4();
            $d=rand(1, 10);
            $v=rand(1, 10);
            $g=rand(0, 1);

            try{
                $this->db->exec("INSERT INTO evaluations (respondent_id, department_id, gender, value) VALUES ('{$r}', '{$d}', {$g}, {$v})");
            }catch (Exception $e){
                $i--;
            }
        }
    }

}