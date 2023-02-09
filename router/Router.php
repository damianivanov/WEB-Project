<?php

class Router {
    public static array $ROUTE = [];
    private $path, $routes;

    public function __construct() {
        $this->path = $_SERVER["REQUEST_URI"];
        // path to the routes file
        $file = APP_ROOT . "router/routes.json";
        // put the content of the file in a variable
        $data = file_get_contents($file);
        // JSON decode
        $this->routes = json_decode($data);
    }

    public function locate(): void
    {
        foreach ($this->routes as $route) {
            //check if current path is equal to path from routes.json
            if ($this->match($route->path, $this->path)) {
                if (isset($route->meta->title)) {
                    Router::$ROUTE['title'] = $route->meta->title;
                }

                if (isset($route->meta->css)) {
                    Router::$ROUTE['css'] = $route->meta->css;
                }
                $this->routeGuard($route);
                return;
            }
        }

        // Redirect the user to the "not found" page
        header("Location: /404");
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION["login_time"]);
    }

    private function routeGuard($route): void
    {
        if (isset($route->meta) && isset($route->meta->auth)) {
            if ($this->isLoggedIn()) {
                if ($route->meta->auth == "prevent") {
                    // Redirect logged-in user to the dashboard
                    header("Location: /dashboard");
                    return;
                }
            } else {
                if ($route->meta->auth == "required") {
                    // Redirect not logged-in user to the login page
                    header("Location: /login");
                    return;
                }
            }
        }

        if (isset(Router::$ROUTE['URL_PARAMS']['id']) && !Course::doesCourseBelongToUser(Router::$ROUTE['URL_PARAMS']['id'])) {
            header("Location: /invalid-course");
            return;
        }

        Router::$ROUTE['view'] = APP_ROOT . "views/" . $route->view . ".php";

        // Load the requested page
        if (isset($route->meta->template)) {
            require_once APP_ROOT . $route->meta->template;
        }
        require_once APP_ROOT . "templates/main.php";
    }

    private function match($route, $subject): bool {
        preg_match_all("#/:([^/]+)/?#", $route, $output);
        $parameter_names = $output[1];

        $search_pattern = "#^". preg_replace("#/:[^/]+(/?)#", "/([^/]+)$1", $route) . "/?$#";
        preg_match_all($search_pattern, $subject, $out);

//        var_dump($out);
        $result = [];
        $i = 1;
        foreach ($parameter_names as $name) {
            // TODO: Fix undefined $out[$i][0]
            //if (isset($out[$i][0]) && count($out[$i][0]) != 0) {
            if(isset($out[$i][0])){
                $result[$name] = $out[$i][0];
        }
        }
            ++$i;
       // }
//        var_dump($result);

        Router::$ROUTE['URL_PARAMS'] = $result;

        return preg_match($search_pattern, $subject);
    }
}
