# Registry

`RegistryTrait` is a PHP trait designed to facilitate the management of objects in a centralized registry. It simulates a class constant system, allowing objects to be stored and accessed efficiently and securely.

## Functionality

This trait allows a class to register and access static objects in an organized manner, as if they were class constants. Key features include object registration, registry initialization and verification, and quick access to registered objects through static methods.

### Main Features:
- **Object Registration**: Allows objects to be registered within the class, ensuring there are no duplicates and that the names follow a specific format.
- **Static Access**: Using the magic method `__callStatic()`, you can access registered objects in a simple way, just like class constants.
- **Validation and Initialization**: The trait ensures objects are registered correctly and automatically initializes the registry when necessary.
- **Customizable Preprocessing**: You can override the member preprocessing function to add specific customizations when returning the registered objects.

## Methods

### `checkInit()`
Ensures that the registry is initialized. If not, it initializes the registry by calling the `setup()` method, which must be implemented by the class using this trait.

### `_registryRegister(string $name, object $member) : void`
Registers a new object in the registry. It ensures that the name is valid and that the object has not been registered previously. Throws an `InvalidArgumentException` if the name is already in use or if the name is invalid.

### `setup() : void`
An abstract method that must be implemented by the class using this trait. It is responsible for initializing the registry with the default members.

### `_registryFromString(string $name) : object`
Retrieves a member from the registry by its name. Throws an `InvalidArgumentException` if the member is not found in the registry.

### `preprocessMember(object $member) : object`
A method responsible for processing a member before returning it. It can be overridden to add custom logic when processing the object.

### `__callStatic(string $name, array $args)`
A magic method that allows you to access registry members statically. If the member is not found or if the number of arguments is incorrect, it will throw an exception.

### `_registryGetAll() : array`
Returns all registered members in the registry.

## How to Use

### 1. **Create a class that uses the `CustomRegistryTrait`**:
   The class should implement the `initializeRegistry()` method, where the default objects will be registered. Additionally, use the `@method` annotation to indicate the static access methods for the registered members.

```php
/**
 * @method static Color RED()
 * @method static Color GREEN()
 */
class Color {
    use RegistryTrait;

    private function __construct(private string $name, private string $hex) {}

    public function getName() : string {
        return $this->name;
    }

    public function getHex() : string {
        return $this->hex;
    }

    // Initialize the registry with default entries
    protected static function setup() : void {
        self::register('red', new self('Red', '#FF0000'));
        self::register('green', new self('Green', '#00FF00'));
    }
}
$red = Color::RED();
echo $red->getName(); // Red

$green = Color::GREEN()->getHex();
echo $green; // #00FF00
