
<?php
# solr search routes

$app->get('/', function () use ($app) {
  $app->render('search/index.html.twig', []);
});


$app->post('/results',function() use ($app){
  // new Solarium Client object
  $solr_client = new Solarium\Client(\SiteConfig::getInstance()->get("solarium_data"));
  $query = $solr_client->createSelect();
  // create a filterquery
  $query->createFilterQuery('clips')->setQuery('type:Clip');
  #$query->createFilterQuery('channels')->setQuery('type:Channel');
  $query_str = Minimalcode\Search\Criteria::where('name_texts')->fuzzy($app->request->post('textsearch'), 0.1)->boost('2.5')
  #->orWhere('subtitle_texts')
  ->andWhere('state_s')->is('published')
  ->andWhere('accessibility_ss')->is('public')
  ->andWhere('is_visible_b')->is(true);
  //TODO replace with text from search form
  $query->setQuery($query_str);
  $resultSet = $solr_client->select($query);
  $app->render('search/results.html.twig', ['result_set' => $resultSet]);
});
