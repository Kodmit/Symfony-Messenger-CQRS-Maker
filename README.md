## Kodmit Messenger REST CRUD Generator

This Symfony bundle allow you to create REST CRUD DTO, handlers and controllers for your entities.

### How to install ?

Simply run  `composer require kodmit/messenger-cqrs-generator`


### How to use ?

#### Generate a CRUD
Once your entity exist, you can generate a CRUD with 

    php bin/console kodmit:make:crud

The following output will appear :

    ~/ (master*) » php bin/console kodmit:make:crud                                                                                                                        alex@MacBook-Pro-de-Alex
    
     Choose an entity:
      [0] App\Entity\User
     > 0
    
     Generating REST CRUD for entity "App\Entity\User"...
    
    Files generated:
     * src/Action/User/CreateUser.php
     * src/Action/User/UpdateUser.php
     * src/Action/User/DeleteUser.php
     * src/Action/User/CreateUserHandler.php
     * src/Action/User/DeleteUserHandler.php
     * src/Action/User/UpdateUserHandler.php
     * src/Controller/UserController.php
    
                                                                                                                            
     [OK] Messenger CRUD and controller generated, now add your own logic :)      

You can now edit the generated files as you need.


#### Generate for a specific scope (create / update / delete)
You can choose the scope with the following command

    php bin/console kodmit:make:create
    php bin/console kodmit:make:update
    php bin/console kodmit:make:delete

A prompt will appear and asking you to choose the entity. The DTO and the handler for the specific scope will be generated and the method will be written in your controller.
