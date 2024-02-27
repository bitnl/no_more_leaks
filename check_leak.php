<?php
/**
 * Check_Leak class for No More Leaks plugin for Roundcube
 * @version 0.5
 * @author Sander Smeenk <sander@bit.nl>
 * @url https://github.com/bitnl/no-more-leaks/
 *
 * This class implements the actual check-a-hash-against-the-dataset function.
 * The class name MUST be Check_Leak. The function name MUST be RunCheck.
 * The function MUST return a boolean of either true or false,
 * with true indicating leaked credentials were detected.
 */
class Check_Leak {
    private array $config;

    /**
     * @param $nml_config, $nml_hash
     * @return boolean
     */
    public function RunCheck(array $nml_config, string $nml_hash): bool {
        $this->config = $nml_config;

        $dsn = $this->config['nml_db_type'];
        if ($this->config['nml_db_type'] === 'sqlite') {
            $dsn .= ":" . $this->config['nml_db_file'];
        } else {
            $dsn .= ":host=" .   $this->config['nml_db_host'] .
                    ";port=" .   $this->config['nml_db_port'] .
                    ";dbname=" . $this->config['nml_db_name'];
        }

        $dbh = new PDO($dsn, $this->config['nml_db_user'], $this->config['nml_db_pass']);

        foreach ($this->config['nml_datasources'] as $datasource) {
            $tbl_name = preg_replace('/[\s\-]+/', '_', strtolower($this->config['nml_db_table_prefix'] . $datasource));

            $sql = "SELECT 1 FROM " . $tbl_name . " WHERE hash = ?";
            $sth = $dbh->prepare($sql);
            $sth->execute([$nml_hash]);
            $rows = $sth->fetchAll();

            if (sizeof($rows) !== 0) {
                $dbh = null;
                return true;
            }
        }

        $dbh = null;
        return false;
    }

}
