<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class MakeUnitTest extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:unit-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Crud';

    protected $type = 'Crud';
    private $crudName;
    private $modelName;

    private $modelsNameSpace = 'Modules\Faculty\Entities\\';
    private $modelFillable;
    private $inputs;
    private $stubDir = 'App/Stubs/Administrators/';
    private $relations;

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $model;

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        if(!$this->argument('name')){
            throw new InvalidArgumentException("Missing required argument crud name");
        }

        if(!$this->argument('model')){
            throw new InvalidArgumentException("Missing required argument model name");
        }

        $this->setCrudVariables();
        $this->CreateFactory();
        $this->CreateUnittest();

//        $this->call('test');
    }


    private function CreateFactory(){
        $stub = $this->files->get($this->stubDir.'DummyModelFactory.stub');
        $stub = $this->replaceAny(
            ['DummyModel','DummyFactoryFillable'],
            [$this->modelName,$this->ModelFactoryFillable()],
            $stub
        );
        $this->files->put(base_path('database/Factories/').ucfirst($this->crudName).'Factory.php', $stub);
    }



    private function CreateUnittest(){
        $stub = $this->files->get($this->stubDir.'DummyUnitTest.stub');
        $stub = $this->replaceAny(
            [
                'DummyUnitTestCrud',
                'DummyCrudName',
                'DummyModel',
                'DummyAssertSee',
                'DummyUpdateFillable',
                'DummyAssertDatabaseHasForCreate',
                'DummyAssertDatabaseHas',
                'DummyAssertDatabaseMissing'
            ],
            [
                ucfirst($this->crudName).'Test',
                $this->crudName,
                $this->modelName,
                $this->AssertSee(),
                $this->UpdateFillable(),
                $this->AssertDatabaseHas('create'),
                $this->AssertDatabaseHas('update'),
                $this->AssertDatabaseMissing()
            ],
            $stub
        );
        $this->files->put(base_path('tests/Feature/').ucfirst($this->crudName).'Test.php', $stub);
    }


    protected function alreadyExists($filePath){
        return $this->files->exists($filePath);
    }


    public function InputFiledLabel($fillable){
        if(strpos($fillable,'[')!==false){
            return str_replace(["['ar']","['en']"],[' ( Arabic )',' ( English )'],$fillable);
        } else
            return $fillable;
    }

    private function setCrudVariables()
    {
        $this->crudName = strtolower($this->argument('name'));
        $this->modelName = $this->argument('model');
        $name = ucwords($this->crudName);
        $this->model = $this->modelsNameSpace.$this->argument('model');
        $this->modelFillable = array_diff((new $this->model)->getFillable(), (new $this->model)->getHidden());
        $NewFillable = [];
        $model = $this->modelsNameSpace . $this->modelName;
        foreach ($this->modelFillable as $fillable){
            if($this->endsWith($fillable,'_id')){
                $modelName = Str::camel(str_replace('_',' ',substr($fillable,0,-3)));
                if($modelName == 'parent') {
                    $modelName = $this->modelName;
                }
            }
            if (isset((new $model)->translatable) && array_search($fillable,(new $model)->translatable) !== false) {
                foreach(config('app.locales') as $locale){
                    $NewFillable[] = $fillable.'[\''.$locale.'\']';
                }
            } else {
                $NewFillable[] = $fillable;
            }
        }
        $this->inputs = $NewFillable;
        $this->GetExtraUse();
    }


    public function ModelFactoryFillable(){
        $factoryFillable = [];
        $model = $this->modelsNameSpace . $this->modelName;
        foreach($this->modelFillable as $fillable){
            if($fillable == 'status'){
                $factoryFillable[] = '        \''.$fillable.'\'          =>          rand(0,1),';
            } elseif (isset($this->relations[$fillable])) {
                $Model = 'Database\Factories\\'.ucfirst($this->relations[$fillable]['modelName']).'Factory';
                $factoryFillable[] = '        "'.$fillable.'"          =>          '.$Model.'::new()->create([]),';
            } else {
                if (isset((new $model)->translatable) && array_search($fillable,(new $model)->translatable) !== false) {
                    $factoryFillable[] = '        "'.$fillable.'"          =>          [\'en\'=>$this->faker->name,\'ar\'=>$this->faker->name],';
                } else
                    $factoryFillable[] = '        "'.$fillable.'"          =>          $this->faker->name,';
            }
        }
        $model = $this->modelsNameSpace.$this->modelName;
        foreach ((new $model)->getHidden() as $hidden){
            $factoryFillable[] = '        \''.$hidden.'\'          =>          $faker->name,';
        }
        return implode(PHP_EOL,$factoryFillable);
    }

    public function AssertSee(){
        $AssertSee = [];
        foreach($this->modelFillable as $fillable){
                $AssertSee[] = '        $response->assertSee($'.$this->crudName.'->'.$fillable.');';
        }
        return implode(PHP_EOL,$AssertSee);
    }

    public function UpdateFillable(){
        $UpdateFillable = [];
        foreach($this->modelFillable as $fillable){
            if(($fillable != 'status') || !isset($this->relations[$fillable])){
                $UpdateFillable[] = '        $Updated["'.$fillable.'"] = $faker->name;';
            }
        }
        return implode(PHP_EOL,$UpdateFillable);
    }




    public function AssertDatabaseHas($type){
        $Model = $this->modelsNameSpace . $this->modelName;
        $table = (new $Model)->getTable();
        $assertDBHas = '        $this->assertDatabaseHas(\''.$table.'\',['.PHP_EOL;
        foreach($this->modelFillable as $fillable){
            if (isset((new $Model)->translatable) && in_array($fillable,(new $Model)->translatable)) {
                $assertDBHas .= '            \''.$fillable.'\'=>json_encode('.($type == 'create' ? '$'.$this->crudName : '$Updated').'->getTranslations(\''.$fillable.'\')),'.PHP_EOL."\t";
            } else
                $assertDBHas .= '            \''.$fillable.'\'=>'.($type == 'create' ? '$'.$this->crudName : '$Updated').'->'.$fillable.','.PHP_EOL."\t";
        }
        $assertDBHas .= '	    ]);'.PHP_EOL;
        return $assertDBHas;
    }

    public function AssertDatabaseMissing(){
        $Model = 'Modules\Faculty\Entities\\'.$this->modelName;
        $table = (new $Model)->getTable();
        $AssertDbMissing = '        $this->assertDatabaseMissing(\''.$table.'\',['.PHP_EOL;
        foreach($this->modelFillable as $fillable){
                $AssertDbMissing .= '\''.$fillable.'\'=>$'.$this->crudName.'->'.$fillable.','.PHP_EOL."\t";
        }
        $AssertDbMissing .= ']);'.PHP_EOL;
        return $AssertDbMissing;
    }

    public function replaceAny($DummyWord,$newName,$stub){
        return str_replace($DummyWord, $newName, $stub);
    }

    public function getStub()
    {
        return  base_path($this->stubDir);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the crud.'],
            ['model', InputArgument::REQUIRED, 'The Model of the crud.'],
        ];
    }

    function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    public function GetExtraUse(){
        if(is_array($this->relations) && count($this->relations)){
            $uses = $relations = [];
            foreach($this->relations as $relation){
                $uses[] = $relation['namespace'];
                $relations[] = $relation['object'];
            }
            // Add service class use
            $this->ExtraUse = implode(PHP_EOL,$uses);
            $this->RelationObjects = implode(PHP_EOL,$relations);
        }
    }

}
