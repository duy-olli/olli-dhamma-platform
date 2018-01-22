<?php
namespace Engine\Db\Model\Annotations;

use Engine\Db\AbstractModel;
use Phalcon\Events\Event;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\User\Plugin as PhPlugin;

/**
 * Annotations initializer.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Initializer extends PhPlugin
{
    /**
     * This is called after initialize the model.
     *
     * @param Event         $event   Event object.
     * @param ModelsManager $manager Model manager
     * @param AbstractModel $model   Model object.
     *
     * @return string
     */
    public function afterInitialize(Event $event, ModelsManager $manager, $model): string
    {
        //Reflector
        $reflector = $this->annotations->get($model);

        /**
         * Read the annotations in the class' docblock
         */
        $annotations = $reflector->getClassAnnotations();
        if ($annotations) {
            /**
             * Traverse the annotations
             */
            foreach ($annotations as $annotation) {
                switch ($annotation->getName()) {
                    /**
                     * Initializes the model's source
                     */
                    case 'Source':
                        $arguments = $annotation->getArguments();
                        $manager->setModelSource($model, $arguments[0]);
                        break;

                    /**
                     * Initializes Has-One relations
                     */
                    case 'HasOne':
                        $arguments = $annotation->getArguments();
                        if (isset($arguments[3])) {
                            $manager->addHasOne($model, $arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                        } else {
                            $manager->addHasOne($model, $arguments[0], $arguments[1], $arguments[2]);
                        }
                        break;

                    /**
                     * Initializes Has-Many relations
                     */
                    case 'HasMany':
                        $arguments = $annotation->getArguments();
                        if (isset($arguments[3])) {
                            $manager->addHasMany($model, $arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                        } else {
                            $manager->addHasMany($model, $arguments[0], $arguments[1], $arguments[2]);
                        }
                        break;

                    /**
                     * Initializes BelongsTo relations
                     */
                    case 'BelongsTo':
                        $arguments = $annotation->getArguments();
                        if (isset($arguments[3])) {
                            $manager->addBelongsTo($model, $arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                        } else {
                            $manager->addBelongsTo($model, $arguments[0], $arguments[1], $arguments[2]);
                        }
                        break;

                    /**
                     * Initializes the model's Behavior
                     */
                     case 'Behavior':
                        $arguments = $annotation->getArguments();
                        $behaviorName = $arguments[0];
                        if (isset($arguments[1])) {
                            $manager->addBehavior($model, new $behaviorName($arguments[1]));
                        } else {
                            $manager->addBehavior($model, new $behaviorName);
                        }
                        break;

                    /**
             		 * Initializes the model's source connection
                     */
                    case 'setConnectionService':
                        $arguments = $annotation->getArguments();
                        $manager->setConnectionService($model, $arguments[0]);
                        break;
                }
            }
        }

        return $event->getType();
    }
}
