<?php namespace core\session;

class SessionMysql implements SessionInterface
{

    private $link; //Mysql数据库连接
    private $table; //SESSION表
    private $expire; //过期时间

    /*
     * 构造函数
     */
    public function __construct()
    {
    }

    //初始
    public function make()
    {
        $options = Config::get('session.mysql');

        //数据表
        $this->table = $options['table'];

        //过期时间
        $this->expire = 864000;

        //连接Mysql
        $this->link = mysql_connect($options['host'], $options['user'], $options['password']);
        //选择数据库
        $db = mysql_select_db($options['database'], $this->link);
        if (!$this->link || !$db) {
            return false;
        }

        mysql_query("SET NAMES UTF8");

        session_set_save_handler(
            [&$this, "open"],
            [&$this, "close"],
            [&$this, "read"],
            [&$this, "write"],
            [&$this, "destroy"],
            [&$this, "gc"]
        );
    }

    /*
     * session_start()时执行
     * @return boolean
     */
    public function open()
    {
        return true;
    }

    /*
     * 读取SESSION 数据
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $sql = "SELECT data FROM " . $this->table . " WHERE sessid='$id' AND atime>" . (NOW - $this->expire);
        $result = mysql_query($sql, $this->link);
        if ($result) {
            $data = mysql_fetch_assoc($result);
            return $data['data'];
        }
        return '';
    }

    /*
     * 写入SESSION
     * @param key $id key名称
     * @param mixed $data 数据
     * @return bool
     */
    public function write($id, $data)
    {
        $sql = "REPLACE INTO " . $this->table . "(sessid,data,atime) ";
        $sql .= "VALUES('$id','$data'," . NOW . ')';
        return mysql_query($sql, $this->link);
    }

    /*
     * 卸载SESSION
     * @param type $id
     * @return type
     */
    public function destroy($id)
    {
        $sql = "DELETE FROM " . $this->table . " WHERE sessid='$id'";
        return mysql_query($sql, $this->link);
    }

    public function close()
    {
        if (mt_rand(1, 100) == 10) {
            $this->gc();
        }
        //关闭数据库连接
        return mysql_close($this->link);
    }


    //关闭SESSION

    /*
     * SESSION垃圾处理
     * @return boolean
     */
    public function gc()
    {

        $sql = "DELETE FROM " . $this->table . " WHERE atime<" . (NOW - $this->expire) . " AND sessid<>'" . session_id() . "'";
        return mysql_query($sql, $this->link);
    }

    public function __destruct()
    {
    }
}
