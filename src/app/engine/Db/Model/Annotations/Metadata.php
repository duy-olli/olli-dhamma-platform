<?php
namespace Engine\Db\Model\Annotations;

use Phalcon\Db\Column;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\MetaData as PhalconMetadata;
use Phalcon\Mvc\ModelInterface;

/**
 * Annotations metadata reader.
 *
 * @category  OLLI Music Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://olli.vn/
 */
class Metadata
{
    /**
     * Initializes the model's meta-data.
     *
     * @param ModelInterface $model Model object.
     * @param DiInterface    $di    Dependency injection.
     *
     * @return array
     */
    public function getMetaData(ModelInterface $model, DiInterface $di): array
    {
        $reflection = $di->get('annotations')->get($model);
        $properties = $reflection->getPropertiesAnnotations();

        $attributes = [];
        $nullables = [];
        $dataTypes = [];
        $dataTypesBind = [];
        $numericTypes = [];
        $primaryKeys = [];
        $nonPrimaryKeys = [];
        $identity = null;

        foreach ($properties as $name => $collection) {
            if ($collection->has('Column')) {
                $arguments = $collection->get('Column')->getArguments();
                $columnName = $this->_getColumnName($name, $arguments);
                $attributes[] = $columnName;

                /**
                 * Check for the 'type' parameter in the 'Column' annotation
                 */
                if (isset($arguments['type'])) {
                    switch ($arguments['type']) {
                        case 'integer':
                            $dataTypes[$columnName] = Column::TYPE_INTEGER;
                            $dataTypesBind[$columnName] = Column::BIND_PARAM_INT;
                            $numericTypes[$columnName] = true;
                            break;
                        case 'string':
                            $dataTypes[$columnName] = Column::TYPE_VARCHAR;
                            $dataTypesBind[$columnName] = Column::BIND_PARAM_STR;
                            break;
                        case 'text':
                            $dataTypes[$columnName] = Column::TYPE_TEXT;
                            $dataTypesBind[$columnName] = Column::BIND_PARAM_STR;
                            break;
                        case 'boolean':
                            $dataTypes[$columnName] = Column::TYPE_BOOLEAN;
                            $dataTypesBind[$columnName] = Column::BIND_PARAM_BOOL;
                            break;
                        case 'date':
                            $dataTypes[$columnName] = Column::TYPE_DATE;
                            $dataTypesBind[$columnName] = Column::BIND_PARAM_STR;
                            break;
                        case 'datetime':
                            $dataTypes[$columnName] = Column::TYPE_DATETIME;
                            $dataTypesBind[$columnName] = Column::BIND_PARAM_STR;
                            break;
                    }
                } else {
                    $dataTypes[$columnName] = Column::TYPE_VARCHAR;
                    $dataTypesBind[$columnName] = Column::BIND_PARAM_STR;
                }

                /**
                 * Check for the 'nullable' parameter in the 'Column' annotation
                 */
                if (!$collection->has('Identity') && !empty($arguments['nullable'])) {
                    $nullables[] = $columnName;
                }

                /**
                 * Check if the attribute is marked as primary
                 */
                if ($collection->has('Primary')) {
                    $primaryKeys[] = $columnName;
                } else {
                    $nonPrimaryKeys[] = $columnName;
                }

                /**
                 * Check if the attribute is marked as identity
                 */
                if ($collection->has('Identity')) {
                    $identity = $columnName;
                }
            }
        }

        return [
            //Every column in the mapped table.
            PhalconMetadata::MODELS_ATTRIBUTES => $attributes,

            //Every column part of the primary key.
            PhalconMetadata::MODELS_PRIMARY_KEY => $primaryKeys,

            //Every column that isn't part of the primary key.
            PhalconMetadata::MODELS_NON_PRIMARY_KEY => $nonPrimaryKeys,

            //Every column that doesn't allows null values.
            PhalconMetadata::MODELS_NOT_NULL => $nullables,

            //Every column and their data types.
            PhalconMetadata::MODELS_DATA_TYPES => $dataTypes,

            //The columns that have numeric data types.
            PhalconMetadata::MODELS_DATA_TYPES_NUMERIC => $numericTypes,

            //The identity column, use boolean false if the model doesn't have
            // an identity column.
            PhalconMetadata::MODELS_IDENTITY_COLUMN => $identity,

            //How every column must be bound/casted.
            PhalconMetadata::MODELS_DATA_TYPES_BIND => $dataTypesBind,

            //Fields that must be ignored from INSERT SQL statements.
            PhalconMetadata::MODELS_AUTOMATIC_DEFAULT_INSERT => [],

            //Fields that must be ignored from UPDATE SQL statements.
            PhalconMetadata::MODELS_AUTOMATIC_DEFAULT_UPDATE => [],

            // Default values for columns
            PhalconMetadata::MODELS_DEFAULT_VALUES => [],

            // Fields that allow empty strings
            PhalconMetadata::MODELS_EMPTY_STRING_VALUES => []
        ];
    }

    /**
     * Initializes the model's column map.
     *
     * @param ModelInterface $model Model object.
     * @param DiInterface    $di    Dependency injection.
     *
     * @return array
     */
    public function getColumnMaps(ModelInterface $model, DiInterface $di)
    {
        $reflection = $di->get('annotations')->get($model);
        $columnMap = [];
        $reverseColumnMap = [];

        $renamed = false;
        foreach ($reflection->getPropertiesAnnotations() as $name => $collection) {
            if ($collection->has('Column')) {
                $arguments = $collection->get('Column')->getArguments();
                $columnName = $this->_getColumnName($name, $arguments);
                $columnMap[$columnName] = $name;
                $reverseColumnMap[$name] = $columnName;

                if (!$renamed) {
                    if ($columnName != $name) {
                        $renamed = true;
                    }
                }
            }
        }

        if ($renamed) {
            return [
                PhalconMetadata::MODELS_COLUMN_MAP => $columnMap,
                PhalconMetadata::MODELS_REVERSE_COLUMN_MAP => $reverseColumnMap
            ];
        }

        return null;
    }

    /**
     * Get metadata from all models in modules.
     *
     * @param DiInterface $di Dependency Injection.
     *
     * @return array
     */
    public function getAllModelsMetadata(DiInterface $di)
    {
        $models = [];
        $registry = $di->get('registry');
        foreach ($registry->modules as $module) {
            $module = ucfirst($module);
            $modelsDirectory = $registry->directories->modules . $module . '/Model';
            foreach (glob($modelsDirectory . '/*.php') as $modelPath) {
                $modelInfo = [];
                $modelClass = '\\' . $module . '\Model\\' . basename(str_replace('.php', '', $modelPath));
                $reflector = $di->get('annotations')->get($modelClass);

                // Get table name.
                $annotations = $reflector->getClassAnnotations();
                if ($annotations) {
                    foreach ($annotations as $annotation) {
                        if ($annotation->getName() == 'Source') {
                            $arguments = $annotation->getArguments();
                            $modelInfo['name'] = $arguments[0];
                        }
                    }
                }

                // Get table fields properties.
                $modelInfo['columns'] = [];
                $models[] = $this->_parseProperties($reflector->getPropertiesAnnotations(), $modelInfo);
            }
        }

        return $models;
    }

    /**
     * Parse model properties.
     *
     * @param array $properties Properties collection.
     * @param array $modelInfo  Model info.
     *
     * @return array
     */
    protected function _parseProperties($properties, $modelInfo)
    {
        foreach ($properties as $name => $collection) {
            if (!$collection->has('Column')) {
                continue;
            }

            $arguments = $collection->get('Column')->getArguments();
            $columnName = $this->_getColumnName($name, $arguments);
            $modelInfo['columns'][$columnName] = [];

            /**
             * Get type.
             */
            if (isset($arguments['type'])) {
                switch ($arguments['type']) {
                    case 'integer':
                        $modelInfo['columns'][$columnName]['type'] = Column::TYPE_INTEGER;
                        $modelInfo['columns'][$columnName]['is_numeric'] = true;
                        break;
                    case 'string':
                        $modelInfo['columns'][$columnName]['type'] = Column::TYPE_VARCHAR;
                        break;
                    case 'text':
                        $modelInfo['columns'][$columnName]['type'] = Column::TYPE_TEXT;
                        break;
                    case 'boolean':
                        $modelInfo['columns'][$columnName]['type'] = Column::TYPE_BOOLEAN;
                        break;
                    case 'date':
                        $modelInfo['columns'][$columnName]['type'] = Column::TYPE_DATE;
                        break;
                    case 'datetime':
                        $modelInfo['columns'][$columnName]['type'] = Column::TYPE_DATETIME;
                        break;
                }
            }

            /**
             * Get size.
             */
            if (isset($arguments['size'])) {
                $modelInfo['columns'][$columnName]['size'] = $arguments['size'];
            }

            /**
             * Check for the 'nullable' parameter in the 'Column' annotation.
             */
            if (!$collection->has('Identity') && !empty($arguments['nullable'])) {
                $modelInfo['columns'][$columnName]['nullable'] = $arguments['nullable'];
            }

            /**
             * Check if the attribute is marked as primary
             */
            if ($collection->has('Primary')) {
                $modelInfo['columns'][$columnName]['is_primary'] = true;
            }

            /**
             * Check if the attribute is marked as identity
             */
            if ($collection->has('Identity')) {
                $modelInfo['columns'][$columnName]['identity'] = true;
            }
        }

        return $modelInfo;
    }

    /**
     * Choose column name.
     *
     * @param string $name      Column name from code.
     * @param array  $arguments Column arguments.
     *
     * @return mixed
     */
    protected function _getColumnName($name, $arguments)
    {
        if (isset($arguments['column'])) {
            return $arguments['column'];
        } else {
            return $name;
        }
    }
}
