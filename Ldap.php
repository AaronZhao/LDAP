<?php
    /**
     * Ldap 操作类
     *
     * @descrition Ldap 操作类
     * @author zhaowei
     * @version 2017-07-10
     *
     */

    class Ldap
    {

        private static $instance;

        const OK = 0;
        const EXP_PARAM = 1001;
        const EXP_LOGIN_FAIL = 1002;
        const EXP_SEARCH = 1003;
        const ERR_CONNECT = 2001;
        const ERR_EXT_NOT_AVAILABLE = 2002;
        const ERR_GET_ENTRY = 2003;
        const ERR_GET_VALUE = 2004;
        const ERR_GET_DN = 2005;

        //LDAP 服务器地址
        private $ldapHost;
        //LDAP 服务器端口
        private $ldapPort;
        //LDAP 服务器 baseDn
        private $baseDn;
        //LDAP 连接标识
        private $linkIdentifier;
        //LDAP DN
        private $dn;
        //LDAP entry identifier
        private $entryIdentifier;

        /**
         * 获取LDAP事例
         * @param string $ldapHost 主机地址
         * @param int $ldapPort 主机端口
         * @param string $baseDn BaseDN
         * @param string $filter 过滤表达式
         * @param int $errCode 错误代码
         *
         * @return Ldap
         */
        public static function getInstance( $ldapHost, $ldapPort, $baseDn, &$errCode )
        {
            if ( false === function_exists("ldap_connect")) {
                $errCode = self::ERR_EXT_NOT_AVAILABLE;
                return null;
            }

            if (empty($ldapHost) || $ldapPort <= 0 || empty($baseDn)) {
                $errCode = self::EXP_PARAM;
                return null;
            }

            if (! (self::$instance instanceof self) ) {
                self::$instance = new self($ldapHost, $ldapPort, $baseDn);
            }
            return self::$instance;

        }

        /**
         * 登陆验证
         *
         * @param $username
         * @param $password
         * @param $errCode
         *
         * @return bool
         */
        public function login( $username, $password, &$errCode )
        {

            if (empty($username) || empty($password)) {
                $errCode = self::EXP_PARAM;
                return false;
            }

            if (false === $this->connect()) {
                $errCode = self::ERR_CONNECT;
                return false;
            }

            if (false === ldap_bind( $this->linkIdentifier, $username, $password ) ) {
                $errCode = self::EXP_LOGIN_FAIL;
                return false;
            }

            $res = ldap_search( $this->linkIdentifier, $this->baseDn, "(|(CN=$username)(UserPrincipalName=$username))");
            if ( false === $res ) {
                $errCode = self::EXP_SEARCH;
                return false;
            }
            $this->entryIdentifier = ldap_first_entry($this->linkIdentifier, $res);
            if ( false === $this->entryIdentifier ) {
                $errCode = self::ERR_GET_ENTRY;
                return false;
            }

            $this->dn = ldap_get_dn($this->linkIdentifier, $this->entryIdentifier);
            if ( false === $this->dn ) {
                $errCode = self::ERR_GET_DN;
                return false;
            }
            return true;


        }

        /**
         * 获取用户姓名
         *
         * @return mixed
         */
        public function getName()
        {
            $res = ldap_get_values( $this->linkIdentifier, $this->entryIdentifier, "name" );
            if( false === $res ) {
                return false;
            }
            return $res[0];
        }

        /**
         * 获取用户职位
         *
         * @return mixed
         */
        public function getTitle()
        {
            $res = ldap_get_values( $this->linkIdentifier, $this->entryIdentifier, "title" );
            if( false === $res ) {
                return false;
            }
            return $res[0];
        }

        /**
         * 获取用户部门
         *
         * @return mixed
         */
        public function getDepartment()
        {
            $res = ldap_get_values( $this->linkIdentifier, $this->entryIdentifier, "department" );
            if( false === $res ) {
                return false;
            }
            return $res[0];
        }

        /**
         * 获取用户完整部门信息
         *
         * @return mixed
         */
        public function getFullDepartment()
        {
            $res = preg_match_all("/OU=(.*?),/",$this->dn,$matches);
            if ( false === $res ) {
                return false;
            }
            if ($res = 0) {
                return "";
            }
            return implode("-", $matches[1]);
        }

        /**
         *  连接 LDAP 服务
         *
         * @return bool
         */
        private function connect()
        {
            $this->linkIdentifier = ldap_connect( $this->ldapHost, $this->ldapPort );
            if ($this->linkIdentifier === false ) return false;

            @ldap_set_option($this->linkIdentifier, LDAP_OPT_PROTOCOL_VERSION, 3);
            @ldap_set_option($this->linkIdentifier,LDAP_OPT_REFERRALS, 0 );
            return true;
        }

        private function __construct($ldapHost, $ldapPort, $baseDn)
        {
            $this->ldapHost = $ldapHost;
            $this->ldapPort = $ldapPort;
            $this->baseDn   = $baseDn;
        }

        private function __clone()
        {
            // TODO: Implement __clone() method.
        }

        public function __destruct()
        {
            // TODO: Implement __destruct() method.
            @ldap_unbind( $this->linkIdentifier);
        }
    }
