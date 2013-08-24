<?

require_once '../source/sys/init.php';

class NiftyTestSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        NF::config()->database->database = 'test';
        
        NF::config()->database->test_type = 'MySQL';
        NF::config()->database->test_host = '127.0.0.1';
        NF::config()->database->test_login = 'nifty_test';
        NF::config()->database->test_database = 'nifty_test';
        NF::config()->database->test_password = 'nifty_test';

        $suite = new NiftyTestSuite('Nifty');
        $tests = array_merge(
            glob('sys/*Test.php'),
            glob('sys/*/*Test.php')
        );

        foreach($tests as $test)
        {
            require_once $test;
            $test = basename($test, '.php');
            $suite->addTestSuite($test);
        }

        return $suite;
    }

    protected function dropAllTables()
    {
        $tables = NF::db()->querySingleValueArray('show tables');

        foreach($tables as $table)
            if (substr($table, 0, 4) == 'test')
                NF::db ()->execute('drop table if exists ' . $table);
    }

    protected function setUp()
    {
        NF::config()->database->database = 'test';

        $this->dropAllTables();

        $queries = explode(';', file_get_contents('setup.sql'));
        foreach($queries as $q)
            if (trim($q))
                NF::db()->execute($q);
    }

    protected function tearDown()
    {
        NF::config()->database->database = 'test';

        $this->dropAllTables();

        $queries = explode(';', file_get_contents('teardown.sql'));
        foreach($queries as $q)
            if (trim($q))
                NF::db()->execute($q);
    }
}
