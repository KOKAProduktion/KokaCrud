<?php

namespace KokaCrud\Patch;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Zend\Loader\StandardAutoloader;

class DoctrineOrmAnnotationDriver extends AnnotationDriver {

    //put your code here

    public function getAllClassNames() {
        if ($this->classNames !== null) {
            return $this->classNames;
        }

        if (!$this->paths) {
            throw MappingException::pathRequired();
        }

        $requiredFiles = get_required_files();

        $classes = array();
        $includedFiles = array();

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new \RegexIterator(
                    new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::LEAVES_ONLY
                    ), '/^.+' . preg_quote($this->fileExtension) . '$/i', \RecursiveRegexIterator::GET_MATCH
            );

            foreach ($iterator as $file) {
                $sourceFile = $file[0];

                if (!preg_match('(^phar:)i', $sourceFile)) {
                    $sourceFile = realpath($sourceFile);
                }

                foreach ($this->excludePaths as $excludePath) {
                    $exclude = str_replace('\\', '/', realpath($excludePath));
                    $current = str_replace('\\', '/', $sourceFile);
                    if (strpos($current, $exclude) !== false) {
                        continue 2;
                    }
                }


                var_dump($requiredFiles[340]);
                var_dump($sourceFile);
                if ((string)$sourceFile == (string)$requiredFiles[340]) {
                    echo "yo";
                }

                require_once $sourceFile;

                $includedFiles[] = $sourceFile;
            }
        }

        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $rc = new \ReflectionClass($className);
            $sourceFile = $rc->getFileName();
            if (in_array($sourceFile, $includedFiles) && !$this->isTransient($className)) {
                $classes[] = $className;
            }
        }

        $this->classNames = $classes;

        return $classes;
    }

}

?>
