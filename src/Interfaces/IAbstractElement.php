<?php declare(strict_types=1);

namespace AdminElement\Elements;

/**
 * Interface IAbstractElement
 *
 * @author  geniv
 * @package AdminElement
 */
interface IAbstractElement extends IElement
{

    /**
     * __toString.
     *
     * @return string
     */
    public function __toString(): string;


    /**
     * Get id element.
     *
     * @return string
     */
    public function getIdElement(): string;


    /**
     * Get configure.
     *
     * @return array
     */
    public function getConfigure(): array;


    /**
     * Set flag success insert.
     *
     * @param int $value
     * @return int
     */
    public function setFlagSuccessInsert(int $value): int;


    /**
     * Set flag success update.
     *
     * @param int $value
     * @return int
     */
    public function setFlagSuccessUpdate(int $value): int;


    /**
     * Set flag success delete.
     *
     * @param int $value
     * @return int
     */
    public function setFlagSuccessDelete(int $value): int;


    /**
     * Set defaults.
     *
     * @param array $values
     * @return mixed|null
     */
    public function setDefaults(array $values);


    /**
     * Pre process ignore values.
     * Define key for array which will be ignored.
     *
     * @return array
     */
    public function preProcessIgnoreValues(): array;


    /**
     * Pre process insert values.
     *
     * @param array $values
     * @return mixed|null
     */
    public function preProcessInsertValues(array $values);


    /**
     * Post process success insert.
     *
     * @param array $values
     */
    public function postProcessSuccessInsert(array $values);


    /**
     * Post process insert.
     *
     * @param array $values
     */
    public function postProcessInsert(array $values);


    /**
     * Pre process update values.
     *
     * @param array $values
     * @return string|null
     */
    public function preProcessUpdateValues(array $values);


    /**
     * Post process success update.
     *
     * @param array $values
     */
    public function postProcessSuccessUpdate(array $values);


    /**
     * Post process update.
     *
     * @param array $values
     */
    public function postProcessUpdate(array $values);


    /**
     * Pre process delete.
     *
     * @param int $id
     */
    public function preProcessDelete(int $id);


    /**
     * Post process success delete.
     *
     * @param int $id
     */
    public function postProcessSuccessDelete(int $id);


    /**
     * Post process delete.
     *
     * @param int $id
     */
    public function postProcessDelete(int $id);
}
