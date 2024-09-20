
# Simple-Dto

DTOs (Data Transfer Objects) are objects that help facilitate the transfer of data between application layers, ensuring a structured and predictable format for the data being passed around.

Why Use DTOs?

Data Transfer Objects improve the separation of concerns by isolating data representation from the core business logic, making your code more maintainable and robust. They act as a layer between controllers, services, and models, providing more control over the data structure and improving security by limiting exposed data fields.

## Features
* Simplifies the creation and management of DTOs in Laravel.
* Enforces strict typing and validation of data.
* Integrates seamlessly into your Laravel projects without deviating from the framework's core conventions.
* Allowing form request validation fields to be derived automatically from the DTO properties, ensuring consistency and reducing duplication.
## Installation
```bash
composer require hosseinkalateh/simple-dto
```
## Usage
#### Creating a DTO
To create a new DTO class, you can use the following artisan command
```bash
php artisan make:dto UserDTO
```
This command will generate a new DTO class file in the app/DTO directory, ready for you to define the data structure.

You can define typed properties in your DTO outside the constructor:
```
final class UserDto
{
    public ?string $name;
    public string $first_name;
    public string $lastName;
    public string $SurName;
}
 ```
Remember that the property name convention can be whatever you want.

#### Creating a Form Request
To create a new Form Request class, you can use the following artisan command
```bash
php artisan make:request-dto RegisterUserRequest --dto=UserDTO
```
In this command the --dto specifies the corresponding dto.

**NOTE :** --dto is required.

This command will generate a From Request file in the appropriate directory with the properties defined in the dto.

```
class RegisterUserRequest extends FormRequest
{
   public function rules()
    {
        return [ 
	    'name' => ['nullable'],
	    'first_name' => ['required'],
	    'last_name' => ['required'],
	    'sur_name' => ['required'],
	];
    }
}
 ```
#### Using DTO

Now in the controller, you can access the request and the DTO also.

```
class UserController
{
    public function register(RegisterUserRequest $request) 
    {
        $request->validated(); // Validated data just like the laravel core structure
        $request->toDto();     // Will return the dto instance
    }
}
 ```
**NOTE :** $request->toDto() will return the DTO instance that we passed to the make request command.
