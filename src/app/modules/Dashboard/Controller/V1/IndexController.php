<?php
namespace Dashboard\Controller\V1;

use Core\Controller\AbstractController;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use Dashboard\Model\Log as LogModel;
use Dashboard\Transformer\Log as LogTransformer;
use Core\Helper\Utils as Helper;

/**
 * Dashboard api.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://olli.vn/
 *
 * @RoutePrefix("/v1/dashboards")
 */
class IndexController extends AbstractController
{
    protected $recordPerPage = 50;

    /**
     * Get all
     *
     * @Route("/", methods={"GET"})
     */
    public function listAction()
    {
        $page = (int) $this->request->getQuery('page', null, 1);
        $formData = [];
        $hasMore = true;

        $page = (int) $this->request->getQuery('page', null, 1);
        $field = (string) $this->request->getQuery('field', null, '');
        $keyword = (string) addslashes($this->request->getQuery('keyword', null, ''));

        $output = [];
        $dir = ROOT_PATH . '/app/storage/logs';
        if (is_dir($dir)) {
            $files = scandir($dir, SCANDIR_SORT_DESCENDING);
            foreach ($files as $file) {
                if (preg_match('/^(.*).log/', $file)) {
                    $lines = file(ROOT_PATH . '/app/storage/logs/' . $file);
                    $contentArr = explode('\n', $lines[0]);
                    krsort($contentArr);

                    if (count($contentArr) > 0) {
                        foreach ($contentArr as $line) {
                            preg_match("/\[(?P<date>[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4} [0-9]{1,2}:[0-9]{1,2} (am|pm))\] \[(?P<logger>[a-z]+)\] \[(?P<level>[A-Z]+)\] (?P<message>[^{]*) (?P<context>.*)/", $line, $matches);

                            if (count($matches) > 0) {
                                $myLog = new LogModel();
                                $myLog->date = $matches['date'];
                                $myLog->logger = $matches['logger'];
                                $myLog->level = $matches['level'];
                                $myLog->message = $matches['message'];
                                $myLog->context = json_decode($matches['context']);
                                $output[] = $myLog;
                            }
                        }
                    }
                }
            }
        }


        if (strlen($field) > 0 && strlen($keyword) > 0) {
            $output = array_filter($output, function (LogModel $myLog) use ($field, $keyword) {
                return preg_match("/\b$keyword\b/i", $myLog->$field);
            });
        }

        // Create paginator object
        $paginator = new \Phalcon\Paginator\Adapter\NativeArray([
            'data' => $output,
            'limit' => $this->recordPerPage,
            'page' => $page
        ]);

        $myLogs = $paginator->getPaginate();

        $output = [];

        // Get sphinx search status
        $output['sphinx']['status'] = true;
        $sphinxClient = new \SphinxClient();
        $sphinxClient->setServer(getenv('SPHINX_HOST'), 9312);

        try {
            $sphinxOutput = $sphinxClient->status();

            $output['sphinx']['host'] = getenv('SPHINX_HOST');
            $output['sphinx']['uptime'] = $sphinxOutput[0][1]; // number of second since server start
            $output['sphinx']['connections'] = $sphinxOutput[1][1]; // number of connection since server start
            $output['sphinx']['maxed_out'] = $sphinxOutput[2][1]; // limited by max_children or by system limits
            $output['sphinx']['command_search'] = $sphinxOutput[3][1]; // number of search command since server start
            $output['sphinx']['queries'] = $sphinxOutput[13][1]; // number of all query since server start
            $output['sphinx']['query_wall'] = $sphinxOutput[15][1]; // time to process query in seconds (all query)
            $output['sphinx']['avg_query_wall'] = $sphinxOutput[23][1]; // average query duration (all query)
        } catch (\Exception $e) {
            $output['sphinx']['status'] = false;
        }

        // Get Beanstalkd status
        $output['beanstalk']['status'] = true;
        try {
            $beanstalkOutput = $this->queue->stats();

            $output['beanstalk']['host'] = getenv('QUEUE_HOST');
            $output['beanstalk']['uptime'] = $beanstalkOutput['uptime'];
            $output['beanstalk']['total-jobs'] = $beanstalkOutput['total-jobs'];
            $output['beanstalk']['current-tubes'] = $beanstalkOutput['current-tubes'];
            $output['beanstalk']['current-producers'] = $beanstalkOutput['current-producers'];
            $output['beanstalk']['current-workers'] = $beanstalkOutput['current-workers'];
            $output['beanstalk']['current-waiting'] = $beanstalkOutput['current-waiting'];
            $output['beanstalk']['current-connections'] = $beanstalkOutput['current-connections'];
            $output['beanstalk']['total-connections'] = $beanstalkOutput['total-connections'];
        } catch (\Exception $e) {
            $output['beanstalk']['status'] = false;
        }

        // Get mysql status
        $mysqlCommand = 'mysqladmin -h '. getenv('DB_HOST') .' -P '. getenv('DB_PORT') .' -u '. getenv('DB_USERNAME') .' -p'. getenv('DB_PASSWORD') .' status';
        $mysqlOutput = shell_exec($mysqlCommand);

        $output['mysql']['host'] = getenv('DB_HOST');

        preg_match('/Uptime: (?P<uptime>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['uptime'] = $matches['uptime']; // The number of seconds the MySQL server has been running

        preg_match('/Questions: (?P<queries_count>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['queries_count'] = $matches['queries_count']; // The number of questions (queries) from clients since the server was started

        preg_match('/Threads: (?P<threads>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['threads'] = $matches['threads']; // The number of active threads

        preg_match('/Slow queries: (?P<slow_queries>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['slow_queries'] = $matches['slow_queries']; // The number of queries that have taken more than long_query_time seconds

        preg_match('/Queries per second avg: (?P<queries_per_second_avg>[0-9\.]+)/', $mysqlOutput, $matches);
        $output['mysql']['queries_per_second_avg'] = $matches['queries_per_second_avg'];

        if ($myLogs->total_pages > 0) {
            if ($page == $myLogs->total_pages) {
                $hasMore = false;
            }

            return $this->createCollection(
                $myLogs->items,
                new LogTransformer,
                'records',
                [
                    'meta' => [
                        'recordPerPage' => $this->recordPerPage,
                        'hasMore' => $hasMore,
                        'totalItems' => $myLogs->total_items,
                        'page' => $page,
                        'services' => $output
                    ]
                ]
            );
        } else {
            return $this->respondWithArray([], 'records');
        }
    }
}
