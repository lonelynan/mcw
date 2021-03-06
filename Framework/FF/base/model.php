<?php

namespace base {
    class model
    {
        private static $pdo = null;
        private $fieldList = array();
        private $auto;
        private $sql = array('field' => '', 'where' => '', 'order' => '', 'limit' => '', 'group' => '', 'having' => '');
        private $tabName = '';
        private $config;
        public $path;

        public function __construct($connection = '')
        {
            if (!extension_loaded('pdo')) {
                throw new \Exception('Db Error: pdo is not install');
            }
            if (!empty($connection)) {
                $this->config = $connection;
            }
        }

        private function _connect()
        {
            if (is_null(self::$pdo)) {
                try {
                    if (isset($this->config['DSN'])) $dsn = $this->config['DSN']; else $dsn = 'mysql:host=' . $this->config['DB_HOST'] . ';dbname=' . $this->config['DB_NAME'];
                    $pdo = new \PDO($dsn, $this->config['DB_USER'], $this->config['DB_PASS'], array(\PDO::ATTR_PERSISTENT => false));
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    $pdo->query('SET NAMES utf8');
                    self::$pdo = $pdo;
                    return $pdo;
                } catch (\PDOException $e) {
                    echo 'db connection fails：' . $e->getMessage();
                }
            } else {
                return self::$pdo;
            }
        }

        public function __set($name, $value)
        {
            if ($name == 'tabName') $this->tabName = $value;
        }

        public function __call($methodName, $args)
        {
            $methodName = strtolower($methodName);
            if (array_key_exists($methodName, $this->sql)) {
                if (empty($args[0]) || (is_string($args[0]) && trim($args[0]) === '')) {
                    $this->sql[$methodName] = '';
                } else {
                    $this->sql[$methodName] = $args;
                }
                if ($methodName == 'limit') {
                    if ($args[0] == '0') $this->sql[$methodName] = $args;
                }
            } else {
                throw new \Exception('调用类' . get_class($this) . '中的方法' . $methodName . '()不存在!');
            }
            return $this;
        }

        public function total()
        {
            $where = '';
            $data = array();
            $args = func_get_args();
            if (count($args) > 0) {
                $where = $this->_comWhere($args);
                $data = $where['data'];
                $where = $where['where'];
            } else if ($this->sql['where'] != '') {
                $where = $this->_comWhere($this->sql['where']);
                $data = $where['data'];
                $where = $where['where'];
            }
            $sql = 'SELECT COUNT(*) as count FROM ' . $this->tabName . $where;
            return $this->query($sql, __METHOD__, $data);
        }

        public function select()
        {
            $fields = (isset($this->sql['field']) && $this->sql['field'] != '') ? $this->sql['field'][0] : implode(',', $this->fieldList);
            $where = '';
            $data = array();
            $args = func_get_args();
            if (count($args) > 0) {
                $where = $this->_comWhere($args);
                $data = $where['data'];
                $where = $where['where'];
            } else if (isset($this->sql['where']) && $this->sql['where'] != '') {
                $where = $this->_comWhere($this->sql['where']);
                $data = $where['data'];
                $where = $where['where'];
            }
            $order = (isset($this->sql['order']) && $this->sql['order'] != '') ? ' ORDER BY ' . $this->sql['order'][0] : ' ORDER BY ' . $this->fieldList['pri'] . ' ASC';
            $limit = (isset($this->sql['limit']) && $this->sql['limit'] != '') ? $this->_comLimit($this->sql['limit']) : '';
            $group = (isset($this->sql['group']) && $this->sql['group'] != '') ? ' GROUP BY ' . $this->sql['group'][0] : '';
            $having = (isset($this->sql['having']) && $this->sql['having'] != '') ? ' HAVING ' . $this->sql['having'][0] : '';
            $sql = 'SELECT ' . $fields . ' FROM ' . $this->tabName . $where . $group . $having . $order . $limit;
            return $this->query($sql, __METHOD__, $data);
        }

        public function find($pri = '')
        {
            $fields = (isset($this->sql['field']) && $this->sql['field'] != '') ? $this->sql['field'][0] : implode(',', $this->fieldList);
            if ($pri == '') {
                $where = $this->_comWhere($this->sql['where']);
                $data = $where['data'];
                $where = (isset($this->sql['where']) && $this->sql['where'] != '') ? $where['where'] : '';
            } else {
                $where = ' where ' . $this->fieldList['pri'] . '=?';
                $data[] = $pri;
            }
            $order = $this->sql['order'] != '' ? ' ORDER BY ' . $this->sql['order'][0] : '';
            $sql = 'SELECT ' . $fields . ' FROM ' . $this->tabName . $where . $order . ' LIMIT 1';
            return $this->query($sql, __METHOD__, $data);
        }

        public function insert($array = null, $filter = 1)
        {
            if (is_null($array)) $array = $_POST;
            $array = $this->_check($array, $filter);
            $sql = 'INSERT INTO ' . $this->tabName . '(' . implode(',', array_keys($array)) . ') VALUES (' . implode(',', array_fill(0, count($array), '?')) . ')';
            return $this->query($sql, __METHOD__, array_values($array));
        }

        public function update($array = null, $filter = 1)
        {
            if (is_null($array)) $array = $_POST;
            $data = array();
            if (is_array($array)) {
                if (array_key_exists($this->fieldList['pri'], $array)) {
                    $pri_value = $array[$this->fieldList['pri']];
                    unset($array[$this->fieldList['pri']]);
                }
                $array = $this->_check($array, $filter);
                $s = '';
                foreach ($array as $k => $v) {
                    $s .= ($k . '=?,');
                    $data[] = $v;
                }
                $s = rtrim($s, ',');
                $setfield = $s;
            } else {
                $setfield = $array;
                $pri_value = '';
            }
            $order = (isset($this->sql['order']) && $this->sql['order'] != '') ? ' ORDER BY ' . $this->sql['order'][0] : '';
            $limit = (isset($this->sql['limit']) && $this->sql['limit'] != '') ? $this->_comLimit($this->sql['limit']) : '';
            if (isset($this->sql['where']) && $this->sql['where'] != '') {
                $where = $this->_comWhere($this->sql['where']);
                $sql = 'UPDATE ' . $this->tabName . ' SET ' . $setfield . $where['where'];
                if (!empty($where['data'])) {
                    foreach ($where['data'] as $v) {
                        $data[] = $v;
                    }
                }
                $sql .= $order . $limit;
            } else {
                $sql = 'UPDATE ' . $this->tabName . ' SET ' . $setfield . ' WHERE ' . $this->fieldList['pri'] . '=?';
                $data[] = $pri_value;
            }
            return $this->query($sql, __METHOD__, $data);
        }

        public function delete()
        {
            $where = '';
            $data = array();
            $args = func_get_args();
            if (count($args) > 0) {
                $where = $this->_comWhere($args);
                $data = $where['data'];
                $where = $where['where'];
            } else if (isset($this->sql['where']) && $this->sql['where'] != '') {
                $where = $this->_comWhere($this->sql['where']);
                $data = $where['data'];
                $where = $where['where'];
            }
            $order = (isset($this->sql['order']) && $this->sql['order'] != '') ? ' ORDER BY ' . $this->sql['order'][0] : '';
            $limit = (isset($this->sql['limit']) && $this->sql['limit'] != '') ? $this->comLimit($this->sql['limit']) : '';
            if ($where == '' && $limit == '') {
                $where = ' where ' . $this->fieldList['pri'] . '=\'\'';
            }
            $sql = 'DELETE FROM ' . $this->tabName . $where . $order . $limit;
            return $this->query($sql, __METHOD__, $data);
        }

        public function query($sql, $method = 'select', $data = array())
        {
            $startTime = microtime(true);
            $this->_setNull();
            $value = $this->_escape_string_array($data);
            $marr = explode('::', $method);
            $method = strtolower(array_pop($marr));
            if (strtolower($method) == trim('total')) {
                $sql = preg_replace('/select.*?from/i', 'SELECT count(*) as count FROM', $sql);
            }
            try {
                $return = null;
                $pdo = $this->_connect();
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($value);
                switch ($method) {
                    case 'select':
                        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                        $return = $data;
                        break;
                    case 'find':
                        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
                        $return = $data;
                        break;
                    case 'total':
                        $row = $stmt->fetch(\PDO::FETCH_NUM);
                        $return = $row[0];
                        break;
                    case 'insert':
                        if ($this->auto == 'yes') $return = $pdo->lastInsertId(); else $return = $result;
                        break;
                    case 'delete':
                    case 'update':
                        $return = $stmt->rowCount();
                        break;
                    default:
                        $return = $result;
                }
                $stopTime = microtime(true);
                $ys = round(($stopTime - $startTime), 4);
                return $return;
            } catch (\PDOException $e) {
                echo 'SQL error: ' . $e->getMessage() . '请查看：' . $this->_sql($sql, $value) . '';
            }
        }

        public function setTable($tabName)
        {
            $this->tabName = $this->config['TABPREFIX'] . $tabName;
            if (!file_exists($this->path . '/' . $tabName . '.php')) {
                try {
                    $pdo = $this->_connect();
                    $stmt = $pdo->prepare('desc ' . $this->tabName);
                    $stmt->execute();
                    $auto = 'yno';
                    $fields = array();
                    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                        if ($row['Key'] == 'PRI') {
                            $fields['pri'] = strtolower($row['Field']);
                        } else {
                            $fields[] = strtolower($row['Field']);
                        }
                        if ($row['Extra'] == 'auto_increment') $auto = 'yes';
                    }
                    if (!array_key_exists('pri', $fields)) {
                        $fields['pri'] = array_shift($fields);
                    }
                    $this->_IO($tabName, '<?php return \'' . json_encode($fields) . $auto . '\';', $this->path);
                    $this->fieldList = $fields;
                    $this->auto = $auto;
                } catch (\PDOException $e) {
                    echo '异常：' . $e->getMessage() . '';
                }
            } else {
                $json = $this->_IO($tabName, null, $this->path);
                $this->auto = substr(ltrim($json, '<?ph '), -3);
                $json = substr($json, 0, -3);
                $this->fieldList = (array)json_decode($json, true);
            }
        }

        public function beginTransaction()
        {
            $pdo = $this->_connect();
            $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
            $pdo->beginTransaction();
        }

        public function commit()
        {
            $pdo = $this->_connect();
            $pdo->commit();
            $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
        }

        public function rollBack()
        {
            $pdo = $this->_connect();
            $pdo->rollBack();
            $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
        }

        public function dbSize()
        {
            $sql = 'SHOW TABLE STATUS FROM ' . $this->config['DB_NAME'];
            if (isset($this->config['TABPREFIX'])) {
                $sql .= ' LIKE \'' . $this->config['TABPREFIX'] . '%\'';
            }
            $pdo = $this->_connect();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $size = 0;
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) $size += $row['Data_length'] + $row['Index_length'];
            return tosize($size);
        }

        public function dbVersion()
        {
            $pdo = $this->_connect();
            return $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        }

        private function _comWhere($args)
        {
            $where = ' WHERE ';
            $data = array();
            if (empty($args)) return array('where' => '', 'data' => $data);
            foreach ($args as $option) {
                if (empty($option)) {
                    $where = '';
                    continue;
                } else if (is_string($option)) {
                    if (!empty($option[0]) && is_numeric($option[0])) {
                        $option = explode(',', $option);
                        $where .= $this->fieldList['pri'] . ' IN(' . implode(',', array_fill(0, count($option), '?')) . ')';
                        $data = $option;
                        continue;
                    } else {
                        $where .= $option;
                        continue;
                    }
                } else if (is_numeric($option)) {
                    $where .= $this->fieldList['pri'] . '=?';
                    $data[0] = $option;
                    continue;
                } else if (is_array($option)) {
                    if (isset($option[0])) {
                        $where .= $this->fieldList['pri'] . ' IN(' . implode(',', array_fill(0, count($option), '?')) . ')';
                        $data = $option;
                        continue;
                    }
                    foreach ($option as $k => $v) {
                        if (!empty($v) && is_array($v)) {
                            $where .= ($k . ' IN(' . implode(',', array_fill(0, count($v), '?')) . ')');
                            foreach ($v as $val) {
                                $data[] = $val;
                            }
                        } else if (strpos($k, ' ')) {
                            $where .= ($k . '?');
                            $data[] = $v;
                        } else if (isset($v[0]) && $v[0] == '%' && substr($v, -1) == '%') {
                            $where .= ($k . ' LIKE ?');
                            $data[] = $v;
                        } else {
                            $where .= ($k . '=?');
                            $data[] = $v;
                        }
                        $where .= ' AND ';
                    }
                    $where = rtrim($where, 'AND ');
                    $where .= ' OR ';
                    continue;
                }
            }
            $where = rtrim($where, 'OR ');
            return array('where' => $where, 'data' => $data);
        }

        private function _comLimit($args)
        {
            if (count($args) == 2) {
                return ' LIMIT ' . $args[0] . ',' . $args[1];
            } else if (count($args) == 1) {
                return ' LIMIT ' . $args[0];
            } else {
                return '';
            }
        }

        private function _escape_string_array($array)
        {
            if (empty($array)) return array();
            $value = array();
            foreach ($array as $val) {
                $value[] = str_replace(array('"', '\''), '', $val);
            }
            return $value;
        }

        private function _setNull()
        {
            $this->sql = array('field' => '', 'where' => '', 'order' => '', 'limit' => '', 'group' => '', 'having' => '');
        }

        private function _check($array, $filter)
        {
            $arr = array();
            foreach ($array as $key => $value) {
                $key = strtolower($key);
                if (in_array($key, $this->fieldList) && $value !== '') {
                    if (!empty($filter) && is_array($filter)) {
                        if (in_array($key, $filter)) {
                            $arr[$key] = $value;
                        } else {
                            $arr[$key] = stripslashes(htmlspecialchars($value));
                        }
                    } else if (!$filter) {
                        $arr[$key] = $value;
                    } else {
                        $arr[$key] = stripslashes(htmlspecialchars($value));
                    }
                }
            }
            return $arr;
        }

        private function _sql($sql, $params_arr)
        {
            if (false === strpos($sql, '?') || count($params_arr) == 0) return $sql;
            if (false === strpos($sql, '%')) {
                $sql = str_replace('?', '\'%s\'', $sql);
                array_unshift($params_arr, $sql);
                return call_user_func_array('sprintf', $params_arr);
            }
        }

        private function _IO($name, $value = null, $path = '')
        {
            static $_cache = array();
            if (isset($_cache[$name . $path])) return $_cache[$name . $path];
            $filename = $path . '/' . $name . '.php';
            if (!is_null($value)) {
                $dir = dirname($filename);
                if (!is_dir($dir)) mkdir($dir);
                file_put_contents($filename, $value);
            }
            if (is_file($filename)) {
                $value = $_cache[$name . $path] = include $filename;
            } else {
                $value = false;
            }
            return $value;
        }
    }
}
