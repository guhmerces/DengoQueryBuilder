# DengoQueryBuilder
DengoQueryBuilder is a personal project. It's a simple query builder using PHP and it's PDO interface.

DengoQueryBuilder é um projeto pessoal. É um query builder em php e usa sua interface PDO.
Esse projeto utiliza PHP 7 e é uma camada básica para comunicação fluente com banco de dados. Surgiu com a necessidade de tornar pequenos trabalhos mais simples e mais produtivos



Exemplo de uso para o Modelo exemplo "Podcast".

Select :

    $podcast = new Podcast();
    
    $podcasts = $podcast->select('title')->where('published_at', '!=', 'null')->limit(10)->get(true);
    
    foreach($podcasts as $podcast) {
        echo $podcast->title;
    }
   
Create : 

    $podcast = new Podcast();
    
    $podcast = $podcast->create([
        'title' => 'Exemple podcast',
        'number' => '1',
        'website' => 'https://foo.bar',
        'published_at' => Carbon::now()
    ]);
    
    var_dump($podcast)  // true
