# Value object mutation solution for Laravel 5.

This package provides an easy way to use Value Objects with your eloquent models. 
A set of mutations is included out of the box in this package, so you can easily upload 
an Image and/or a File as value object and save it on your eloquent model, and you can also
use our Settings value object implementation to save or settings on database.

Here are a few short examples of what you can do:

```php
$user = User::find(1);
$user->profile_photo = $request->file('photo');
```

It can handle your uploads directly like above or fetch directly from an URL:

```php
$user->profile_photo = 'http://www.anyresourceurl.com/some_image.jpg';
```

And you can access them later like this:

```php
echo $user->profile_photo->url; //Returns photo url
echo $user->profile_photo->width; //Get width of image
echo $user->profile_photo->height; //Get height of image

```

## Requirements

To create derived images [GD](http://php.net/manual/en/book.image.php) should be installed on your server.

## Installation

You can install this package via composer using this command:

```bash
composer require felipeweb11/laravel-easymutators
```

Next, you must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    Webeleven\EasyMutators\EasyMutatorsServiceProvider::class,
];
```

You can publish the config-file with:

```bash
php artisan vendor:publish --provider="Webeleven\EasyMutators\EasyMutatorsServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Filesystem Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystems on which to store added media. Choose one of the
    | filesystems you configured in app/config/filesystems.php
    |
    */

    'storage_disk' => env('EASYMUTATORS_STORAGE_DISK', 'easymutators'),

    /*
    |--------------------------------------------------------------------------
    | Default image path generator
    |--------------------------------------------------------------------------
    |
    | Default path generator class, used to generate the of files based on
    | mapping.
    |
    */

    'default_path_generator' => \Webeleven\EasyMutators\Upload\DefaultPathGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | This package uses Intervention Image internally that supports "GD Library"
    | and "Imagick" to process images. You may choose one of them according to
    | your PHP configuration. By default PHP's "GD Library" implementation is
    | used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'image_driver' => 'gd',

    /*
    |--------------------------------------------------------------------------
    | Delete old media
    |--------------------------------------------------------------------------
    |
    | Determine if old media should be deleted from filesystem.
    |
    | Options: false, "on-set", "on-save"
    |
    */

    'delete_old_media' => 'on-save',

    /*
   |--------------------------------------------------------------------------
   | Default objects
   |--------------------------------------------------------------------------
   |
   | Default value objects classes, this is an alias array used to map to value
   | object class.
   |
   */

    'objects' => [
        'file' => \Webeleven\EasyMutators\ValueObjects\File::class,
        'image' => \Webeleven\EasyMutators\ValueObjects\Image::class,
        'settings' => \Webeleven\EasyMutators\ValueObjects\Settings::class
    ],

];
```

And finally you should add a disk to `app/config/filesystems.php`. This would be a typical configuration:

```php
    ...
    'disks' => [
        ...

        'easymutators' => [
            'driver' => 'local',
            'root' => storage_path('app/public/media'),
            'url' => env('APP_URL').'/storage/media',
            'visibility' => 'public',
        ],
    ...
```

## Basic usage

Let's use the User class as an example. First you should create fields on your database. 
Using migrations, you can do this on your users table to create an profile_photo field 
which will be used to store the users profile photo.

```php
class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            
            //If you are using MySQL 5.7 you can use JSON column type instead of text
            $table->text('profile_photo')->nullable()->default(null);
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    ...
}
```

Now you should use the EasyMutatorsTrait on your model and set the mutations fields:

```php
...
use Webeleven\EasyMutators\EasyMutatorsTrait;

class User extends Model
{

    use EasyMutatorsTrait;
    
    // Here you should set the attribute name and type.
    // You can use predefined types of easymutators.php config file
    // or create your own value objects that should implements
    // Webeleven\EloquentValueObject\ValueObjectInterface
    protected $mutations = [
        'profile_photo' => 'image'
    ];
    
    // Add field to fillable
    protected $fillable = [
        ...
        'profile_photo',
    ];

}
```

Now you are ready to easily save an image like this:

```php
$user = User::find(1);
$user->profile_photo = $request->file('photo');

//Or directly from an URL
$user->profile_photo = 'http://anyresourceurl/some_image.jpg';

//And save the user in database
$user->save();

```

## Handling image conversions

The storage of the files is handled by [Laravel's Filesystem](https://laravel.com/docs/5.4/filesystem),
so you can use any filesystem you like. Additionally the package can create 
image conversions for an specific image.
If you want do use this, you can configure image conversions simply by implementing 
the mapMedia() method on your model, like this:

```php
...
use Webeleven\EasyMutators\EasyMutatorsTrait;
use Webeleven\EasyMutators\Mapping\MediaMapper;

class User extends Model
{

    use EasyMutatorsTrait;
    
    protected $mutations = [
        'profile_photo' => 'image'
    ];
    
    protected function mapMedia(MediaMapper $mapper)
    {
    
        $profilePhoto = $mapper->image('profile_photo')
                               ->width(900);
    
        $profilePhoto
            ->addConversion('medium')
            ->width(400);
            
        $profilePhoto
            ->addConversion('small')
            ->width(50)
            ->quality(70);
            
        $profilePhoto
            ->addConversion('other')
            ->name('otherFileName')
            ->width(100)
            ->height(50)
            ->generatePathWith('App\MyOwnPathGenerator::class') // Implements Webeleven\EasyMutators\Upload\PathGenerator
            ->dontKeepAspectRatio();
    }

}
```

And you can access them later like this:

```php
echo $user->profile_photo->medium->url;         //Returns url of medium image
echo $user->profile_photo->medium->width;       //Get width of medium image
echo $user->profile_photo->medium->height;      //Get height of medium image

echo $user->profile_photo->small->url;          //Returns url of small image
echo $user->profile_photo->small->width;        //Get width of small image
echo $user->profile_photo->small->height;       //Get height of small image

//You can also access other attributes like:
echo $user->profile_photo->small->filename;     //Returns filename of image
echo $user->profile_photo->small->name;         //Returns name of image
echo $user->profile_photo->small->path;         //Returns path of image
echo $user->profile_photo->small->size;         //Returns size of image


```

Optionally you can set a custom base upload directory for your model, by default 
the base upload dir is formed by combination of short class name 
of the model + entity primary key (if exists) + short hash.

```php
...
use Webeleven\EasyMutators\EasyMutatorsTrait;
use Webeleven\EasyMutators\Mapping\MediaMapper;

class User extends Model
{

    use EasyMutatorsTrait;
    
    protected $mutations = [
        'profile_photo' => 'image'
    ];
    
    protected function mapMedia(MediaMapper $mapper)
    {
        ...
        
        //Custom base upload dir for this model
        $mapper->baseUploadDir('nameOfDirectory/' + $this->name);
        
        ...
    }

}
```

## Implementing your own value objects

You can create your own value objects by implementing the Webeleven\EloquentValueObject\ValueObjectInterface

```php

interface ValueObjectInterface extends Arrayable
{
    /**
     * @param $value
     */
    public function __construct($value); // Implement logic do construct your value object

    /**
     * @return mixed
     */
    public function toScalar();          // Convert value object to scalar format

    /**
     * @return string
     */
    public function __toString();        // Convert the value objects to database format (string or json string)

    /**
     * @param array $args
     * @return ValueObjectInterface
     */
    public static function make();       // Used to create a new value object instance
}

```

And to use, you can configure the attribute on your model:

```php
...
use Webeleven\EasyMutators\EasyMutatorsTrait;

class User extends Model
{

    use EasyMutatorsTrait;
    
    protected $mutations = [
        'my_attribute' => MyCustomValueObject::class
    ];
    
    // Add field to fillable
    protected $fillable = [
        ...
        'my_attribute',
    ];

}
```

Or you can configure an alias on easymutators.php config file:

```php
'objects' => [
    ...
    
    'my-custom-value-object' => MyCustomValueObject::class
],
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.