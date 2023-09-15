# Simple cards
_____
### REST API app created using:
* PHP 8.1
* Laravel 10.0
* MySql 8.0.30
* Redis 2.0.2
____
### Functionality: 
It`s quite similar to Trello or any Kanban-type app by its functionality.

The following functionality implemented in this project:
- [x] you can register via email and password; 
- [x] create your own boards, add registered team members to it and customize board by adding your background images;
- [x] create your custom columns in boards and move their order;
- [x] add, move, update and filter cards in columns with different settings and tags;
- [x] add custom tags or use basic collection of tags;
- [x] receive notifications by mail, when your card was updated by team members.

All features are covered by tests.
_____
### Installation:

To setup app localy:
1. Use git clone to clone this repository localy 
2. Copy ```.env.example``` file and paste to your ```.env``` file
3. Generate key using command ```php artisan key:generate```
4. Run basic tag seeder by command ```php artisan db:seed --class=BasicAppTagsSeeder```  

To run tests:
1. Copy ```.env.testing.example``` file and paste to your ```.env.testing``` file
2. Use command ```php artisan test``` covered by tests.
