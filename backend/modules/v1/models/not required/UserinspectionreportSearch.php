<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Userinspectionreport;

/**
 * UserinspectionreportSearch represents the model behind the search form of `backend\models\Userinspectionreport`.
 */
class UserinspectionreportSearch extends Userinspectionreport
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inspectionReportId', 'inspectionId'], 'integer'],
            [['reportType', 'status', 'signatureUrl', 'inspectionDescription', 'mediaUrl', 'mediaType', 'createdOn', 'modifiedOn'], 'safe'],
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
        $query = Userinspectionreport::find();

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
            'inspectionReportId' => $this->inspectionReportId,
            'inspectionId' => $this->inspectionId,
            'createdOn' => $this->createdOn,
            'modifiedOn' => $this->modifiedOn,
        ]);

        $query->andFilterWhere(['like', 'reportType', $this->reportType])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'signatureUrl', $this->signatureUrl])
            ->andFilterWhere(['like', 'inspectionDescription', $this->inspectionDescription])
            ->andFilterWhere(['like', 'mediaUrl', $this->mediaUrl])
            ->andFilterWhere(['like', 'mediaType', $this->mediaType]);

        return $dataProvider;
    }
}
