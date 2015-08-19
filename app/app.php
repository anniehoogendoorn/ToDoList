<?php
    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/Task.php";
    require_once __DIR__."/../src/Category.php";

    $app = new Silex\Application();
    $app["debug"] = true;

    //Make sure you have the database below exists in sql, if not, create one.
    $server = 'mysql:host=localhost;dbname=to_do';
    $username = 'root';
    $password = 'root';
    //setting up connection to our database
    $DB = new PDO($server, $username, $password);

    $app->register(new Silex\Provider\TwigServiceProvider(), array(
        'twig.path' => __DIR__.'/../views'
    ));

    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    //This takes the user to "/" page, in which we render our index.html.twig page. It also pushes our categories to be listed.
    $app->get("/", function() use ($app){
        return $app['twig']->render('index.html.twig', array('categories' => Category::getAll()));
    });

    //When the user submits a task description:
    $app->post("/tasks", function() use ($app){
        //It saves the entered description
        $description = $_POST['description'];
        //Attaches the category id
        $category_id = $_POST['category_id'];
        //Attaches the due date
        $due_date = $_POST['due_date'];
        //Creates a new Task object with the above mentioned description, and category_id. Sets the task id to null because it is assigned by the database
        $task = new Task($_POST['description'], $id = null, $category_id, $due_date);
        //Saves new task to database and assigns a task id - see Task.php
        $task->save();
        //Defines the category variable by finding the category by using its id
        $category = Category::find($category_id);
        //Displays the category page with the list of existing tasks
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks()));
    });

    $app->post("/delete_tasks", function() use ($app){
        Task::deleteAll();
        return $app['twig']->render('index.html.twig', array('categories' => Category::getAll()));
    });

    $app->get("/categories/{id}", function($id) use ($app) {
        $category = Category::find($id);
        //This takes the user to /categories/* page, which has a form to add a new task, and  returns the array 'tasks' with existing tasks.
         return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks'=> $category->getTasks()));
    });

    $app->post("/categories", function() use ($app) {
         $category = new Category($_POST['name']);
         $category->save();
         return $app['twig']->render('index.html.twig', array('categories' => Category::getAll()));
    });

    $app->post("/delete_categories", function() use ($app) {
        Category::deleteAll();
        return $app['twig']->render('index.html.twig', array('categories' => Category::getAll()));
    });

    $app->get("/categories/{id}/edit", function($id) use ($app) {
        $category = Category::find($id);
        return $app['twig']->render('category_edit.html.twig', array('category' => $category));
    });

    $app->patch("/categories/{id}", function($id) use ($app) {
        $name = $_POST['name'];
        $category = Category::find($id);
        $category->update($name);
        return $app['twig']->render('category.html.twig', array('category' => $category, 'tasks' => $category->getTasks()));
    });

    $app->delete("/categories/{id}", function($id) use ($app) {
        $category = Category::find($id);
        $category->delete();
        return $app['twig']->render('index.html.twig', array('categories'=> Category::getAll()));
    });



return $app;

?>
