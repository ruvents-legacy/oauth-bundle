<?php

namespace Ruvents\OAuthBundle;

interface DataStorageInterface
{
    /**
     * @return DataInterface
     */
    public function get();

    /**
     * @param DataInterface $data
     *
     * @return void
     */
    public function save(DataInterface $data);

    /**
     * @return void
     */
    public function clear();
}
