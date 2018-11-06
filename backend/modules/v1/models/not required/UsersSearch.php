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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'roleId'], 'integer'],
            [['userFirstName', 'userLastName', 'userEmail', 'userPassword', 'userCompany', 'userDepartment', 'nameToReceiveReport', 'emailToReceiveReport', 'companyLogo', 'profilePicture', 'lastLogin', 'status', 'resetToken', 'resetTokenExpiry', 'createdOn', 'modifiedOn'], 'safe'],
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
            'lastLogin' => $this->lastLogin,
            'roleId' => $this->roleId,
            'resetTokenExpiry' => $this->resetTokenExpiry,
            'createdOn' => $this->createdOn,
            'modifiedOn' => $this->modifiedOn,
        ]);

        $query->andFilterWhere(['like', 'userFirstName', $this->userFirstName])
            ->andFilterWhere(['like', 'userLastName', $this->userLastName])
            ->andFilterWhere(['like', 'userEmail', $this->userEmail])
            ->andFilterWhere(['like', 'userPassword', $this->userPassword])
            ->andFilterWhere(['like', 'userCompany', $this->userCompany])
            ->andFilterWhere(['like', 'userDepartment', $this->userDepartment])
            ->andFilterWhere(['like', 'nameToReceiveReport', $this->nameToReceiveReport])
            ->andFilterWhere(['like', 'emailToReceiveReport', $this->emailToReceiveReport])
            ->andFilterWhere(['like', 'companyLogo', $this->companyLogo])
            ->andFilterWhere(['like', 'profilePicture', $this->profilePicture])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'resetToken', $this->resetToken]);

        return $dataProvider;
    }
}
