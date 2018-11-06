<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Tokens;

/**
 * TokensSearch represents the model behind the search form of `backend\models\Tokens`.
 */
class TokensSearch extends Tokens
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tokenId', 'userId'], 'integer'],
            [['token', 'expiry', 'createdOn', 'modifiedOn'], 'safe'],
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
        $query = Tokens::find();

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
            'tokenId' => $this->tokenId,
            'userId' => $this->userId,
            'expiry' => $this->expiry,
            'createdOn' => $this->createdOn,
            'modifiedOn' => $this->modifiedOn,
        ]);

        $query->andFilterWhere(['like', 'token', $this->token]);

        return $dataProvider;
    }
}
