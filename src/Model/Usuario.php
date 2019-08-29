<?php

namespace Z2Admin\Model;

use Z2Admin\Core\Model;

/**
 * Class Usuario
 * @property int id
 * @property string nome
 * @property string usuario
 * @property string senha
 * @property string login_token
 * @package Z2Admin\Model
 */
class Usuario extends Model
{
    protected $_table = 'usuario';
}