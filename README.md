# nova-wizard
A wizard form for the Laravel Nova


##### Table of Contents   

* [Introduction](#introduction)      
* [Installation](#installation)      
* [Resource Configurations](#resource-configurations)    
* [Storing](#storing)  
* [Ignore Wizard On Update](#ignore-wizard-on-update)  


## Introduction

This package gives you the ability to `creating` or `updating` a resource step by step and `validating` and `storing` resources on each step.

## Installation

To get started with `nova-wizard` run the below command:

```    
    composer require zareismail/nova-wizard
```

## Resource Configurations

To use, your resource class should implement the `Wizard` interface. Then for creating each step use the `Step` class like the following:

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

Now; your source automatically displays wizard form to you.

## Storing

By default; each step, except the last, will store in the session. if you want to store data into the database after a specific step you should call the `Checkpoint` method on that step. now, checkpoint step and all steps after it will be store in the `DB`.

## Ignore Wizard On Update

For ignoring `wizard-form` on the update page, your resource should implement the `Zareismail\NovaWizard\Contracts\IgnoreUpdateWizard`. 
