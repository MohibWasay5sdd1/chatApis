<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Users;

/**
 * UsersSearch represents the model behind the search form of `backend\models\Users`.
 */
class UsersSearch extends Users
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId'], 'integer'],
            [['userName', 'userEmail', 'userPassword', 'userCompany', 'userDepartment', 'nameToReceiveReport', 'emailToReceiveReport', 'createdOn', 'modifiedOn'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = Users::find();

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
            'userId' => $this->userId,
            'createdOn' => $this->createdOn,
            'modifiedOn' => $this->modifiedOn,
        ]);

        $query->andFilterWhere(['like', 'userName', $this->userName])
            ->andFilterWhere(['like', 'userEmail', $this->userEmail])
            ->andFilterWhere(['like', 'userPassword', $this->userPassword])
            ->andFilterWhere(['like', 'userCompany', $this->userCompany])
            ->andFilterWhere(['like', 'userDepartment', $this->userDepartment])
            ->andFilterWhere(['like', 'nameToReceiveReport', $this->nameToReceiveReport])
            ->andFilterWhere(['like', 'emailToReceiveReport', $this->emailToReceiveReport]);

        return $dataProvider;
    }
}
