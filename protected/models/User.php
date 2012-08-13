<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $login
 * @property string $password
 * @property integer $email
 * @property string $salt
 * @property string $key
 * @property integer $is_active
 */
class User extends CActiveRecord
{
    private $salt2 = 'oblom4ik';
    private $userSalt = '';
    private $_id;
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('login, password, email', 'required', 'on'=>'register'),
            array('login, password', 'required', 'on'=>'login'),
            array('login', 'length', 'min'=>3, 'max'=>18),
            array('login', 'match', 'pattern'=>'/^[a-zA-Z0-9-_]+$/', 'on'=>'register', 'message'=>'Недопустимые символы! Допускаются буквы латинского алфавита, цифры, тире и знак подчеркивания '),
            array('login', 'unique', 'on'=>'register', 'message'=>'Данный логин уже занят в системе'),
			array('email', 'email', 'message'=>'Введите правильный email'),
			array('email', 'unique', 'on'=>'register', 'message'=>'Данный email уже зарегистрирован в системе'),
			array('password', 'length', 'min'=>5),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, login, password, email, salt, key, is_active', 'safe', 'on'=>'search'),
		);
	}

    public function getSalt2() {

        return $salt2 = $this->salt2;
    }
    public function setSalt($userSalt) {

        $this->userSalt = $userSalt;
    }
    public function getSalt() {

        return $this->userSalt;
    }

//    public function validatePassword($password)
//    {
//        return $this->hashPassword($password,$this->salt)===$this->password;
//    }

    public function getUser($login) {

        $this::model()->find('login=?',array($login));
    }

    public function login($login, $password) {


        $identity = new UserIdentity($login, $password);

        if($identity->authenticate()) {

            Yii::app()->user->login($identity);
            Yii::app()->user->setFlash('message', 'Вы успешно вошли в систему');
            return true;
        }else {

            switch($identity->errorCode) {
                case 1:
                    Yii::app()->user->setFlash('error_message', 'Пользователь не ннайден в системе');
                    break;
                case 2:
                    Yii::app()->user->setFlash('error_message', 'Логин и пароль не совпадают');
                    break;
                case 3:
                    $email = $identity->getEmail();
                    $key = $identity->getKey();

                    $url = Yii::app()->request->getBaseUrl(true) . "/user/checkEmail?email=$email&key=$key&pas=$password";
                    $res = Sendemail::sendConfirmEmail($email, $url);
                    if($res) {
                        $message = 'Вы зарегестрированы в системе, но Ваш email не подтвержден. ';
                        $message .= "На email: $email выслано сообщение в котором неободимо пройти по ссылке ";
                        $message .= 'для подтверждения Вашего адреса!';

                        Yii::app()->user->setFlash('error_message', $message);
                    }
                    break;
                default:
                    Yii::app()->user->setFlash('error_message', 'Вход не выполнен, повторите попытку');
            }
        }
        return false;
    }

    public function checkEmail($key, $email) {

        if($key and $email) {
            $user = User::model()->find('email=:email', array(
                ':email'=> $email
            ));
            if($user->key == $key){
                $user->is_active = 1;
                if($user->save()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hashPassword($password, $salt) {

        return md5($password . $salt . $this->getSalt2());
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'login' => 'Login',
			'password' => 'Пароль',
			'email' => 'Email',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('login',$this->login,true);
		$criteria->compare('email',$this->email);
		$criteria->compare('is_active',$this->is_active);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}