# nova-wizard
A wizard form for the Laravel Nova


##### Table of Contents   

* [Introduction](#introduction)      
* [Installation](#installation)      
* [Resource Configurations](#resource-configurations)    
* [Storing](#storing)  
* [Ignore Wizard On Update](#ignore-wizard-on-update)  


## Introduction
The `wizard` gives you the ability to creating or updating a resource step by step via validating and storing each step.

## Installation

To get started with `nova-wizard` run the below command:

```    
    composer require zareismail/nova-wizard
```

## Resource Configurations

If you want to use wizard form; your resource should implements the `Wizard` interface. 
Then for create each step use the `Step` class like following:

```     
    use Zareismail\NovaWizard\Contracts\Wizard; 
    use Zareismail\NovaWizard\Step;


    

    class Supply extends Resource implements Wizard
    { 
        return [  
            (new Step(__('Step One'), [      

                // some fields


            ])->withToolbar()


            new Step('Step Two', [
            ]),

            new Step(__('Step Three'), function() {
                return [
                ];
            }),
        ];
    }
``` 

Now; your resource automaticaly display wizard form to you.

## Storing
By default, each step, storing in the session, if you want store data into database after a specific step
you should use `Checkpoint` method. checkpoint step will store in `DB` and after it all steps will be store in `DB` 

## Ignore Wizard On Update
if wnat use wizard form just on creation; your resource should implement the `Zareismail\NovaWizard\Contracts\IgnoreUpdateWizard` interface.
