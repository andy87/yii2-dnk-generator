<?php

$tests = [];
foreach (['backend', 'frontend'] as $side) {
    $tests['routes: ' . $side . ' default page, row links and breadcrumbs resolve'] = static function () use ($side): void {
        Yii::$app->db->createCommand()->insert('user', ['name' => 'Linked record'])->execute();
        $controller = generatedController($side);
        $html = $controller->runAction('');
        expect(strpos($html, 'Linked record') !== false, 'The default route did not render the list.');
        preg_match_all('/href="([^"]+)"[^>]*title="(View|Update|Delete)"/', $html, $links, PREG_SET_ORDER);
        expect(count($links) === ($side === 'backend' ? 2 : 1), 'Unsupported row actions are still shown.');
        foreach ($links as $link) {
            parse_str(parse_url(html_entity_decode($link[1]), PHP_URL_QUERY), $params);
            $action = basename($params['r']);
            expect($action === ($link[2] === 'View' ? 'read' : 'update'), 'The link targets an incorrect action.');
            $page = $controller->runAction($action, ['id' => $params['id']]);
            expect(is_string($page) && $page !== '', 'A row link did not render its page.');
            foreach (Yii::$app->view->params['breadcrumbs'] as $crumb) {
                if (!is_array($crumb) || !isset($crumb['url'])) continue;
                $route = $crumb['url'][0];
                expect($route === 'list', 'The breadcrumb does not point to the list.');
                expect(strpos($controller->runAction($route), 'Linked record') !== false, 'The breadcrumb route does not resolve.');
            }
        }
    };
}
$tests['routes: multiword entities render views under the Yii controller ID'] = static function (): void {
    Yii::$app->db->createCommand()->insert('user', ['name' => 'Multiword record'])->execute();
    $generator = new RegressionGenerator('dnk', Yii::$app);
    ob_start();
    try {
        $generator->actionGenBackendViews('user_profile');
        $generator->actionGenFrontendViews('user_profile');
    } finally {
        ob_end_clean();
    }
    foreach (['backend', 'frontend'] as $side) {
        Yii::$app->setViewPath(Yii::getAlias('@' . $side . '/views'));
        $controller = new yii\web\Controller('user-profile', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $class = $side . '\\services\\UserService';
        $service = new $class();
        $resource = $service->getResource(andy87\dnk\source\base\BaseService::LIST, [
            'gridViewResource' => new andy87\dnk\source\resources\GridViewResource(
                $service->getForm(), new yii\data\ActiveDataProvider(['query' => $service->findWhere([])])
            ),
        ]);
        expect(strpos($controller->render('user_profile-list', ['R' => $resource]), 'Multiword record') !== false, 'The multiword view path does not match the route.');
    }
};
return $tests;
