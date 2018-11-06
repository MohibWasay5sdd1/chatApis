<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\users;

/**
 * usersSearch represents the model behind the search form of `frontend\models\users`.
 */
class usersSearch extends users
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'role_id'], 'integer'],
            [['user_name', 'first_name', 'last_name', 'full_name', 'email', 'password', 'phone_number', 'dob', 'status', 'reset_token', 'reset_expiry', 'profile_pic_url', 'last_login', 'created_on', 'modified_on'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = users::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'dob' => $this->dob,
            'reset_expiry' => $this->reset_expiry,
            'last_login' => $this->last_login,
            'role_id' => $this->role_id,
            'created_on' => $this->created_on,
            'modified_on' => $this->modified_on,
        ]);

        $query->andFilterWhere(['like', 'user_name', $this->user_name])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'full_name', $this->full_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'phone_number', $this->phone_number])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'reset_token', $this->reset_token])
            ->andFilterWhere(['like', 'profile_pic_url', $this->profile_pic_url]);

        return $dataProvider;
    }
}
