<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 研修会ごとの詳細な案内文書・プログラムセット（3パターン）
        $sampleDescriptions = [
            // パターン1: 標準的な対面/Webハイブリッド講習会（目的、タイムスケジュール、単位、注意事項、URL）
            "<h1>【開催案内】令和8年度 第1回 医療安全・感染対策学術研修会</h1>
            <p>医療安全管理体制の強化および最新の感染防止対策に関する知識深耕を目的として、下記のとおり研修会を開催いたします。</p>

            <h2>1. 開催日時および場所</h2>
            <ul>
                <li><strong>日時：</strong>2026年9月15日(火) 19:00〜20:30</li>
                <li><strong>会場：</strong>医師会館 4階講堂（Zoomウェビナーによる同時配信あり）</li>
                <li><strong>定員：</strong>会場 50名 / オンライン 200名（要事前申込）</li>
            </ul>

            <h2>2. プログラム・演題</h2>
            <p><strong>【基調講演】「地域医療機関におけるアウトブレイク対応と現場での工夫」</strong></p>
            <p>講師：〇〇総合病院 感染制御部 部長 山田 太郎 先生</p>
            <ul>
                <li>19:00〜19:05 開会挨拶・主旨説明</li>
                <li>19:05〜20:05 講演および事例紹介</li>
                <li>20:05〜20:30 質疑応答および全体ディスカッション</li>
            </ul>

            <h3>■ 取得可能単位</h3>
            <p>日本医師会生涯教育講座 カリキュラムコード：<span style='color: #2563eb; font-weight: bold;'>8（感染対策）1.5単位</span></p>

            <h3>■ 参加申込および資料取得</h3>
            <p>参加をご希望の方は、事前に以下の登録フォームよりお申し込みください。事前資料も同ページよりダウンロード可能です。</p>
            <p><a href='https://example.com/workshop/register/2026/medical-safety-seminar-entry-form-check-page-test-long-url-path'>https://example.com/workshop/register/2026/medical-safety-seminar-entry-form-check-page-test-long-url-path</a></p>

            <blockquote>
                <p>※Web参加の方には、前日までに登録メールアドレスへZoom参加URLをお送りいたします。</p>
            </blockquote>",

            // パターン2: 制度改正・実務系講習会（中央/右揃え、要点箇条書き、強調表示）
            "<h2>令和8年度 介護保険制度改正に伴う事業説明会</h2>
            <p style=\"text-align: right; color: #4b5563;\">
                主催：介護保険委員会<br>
                担当：医療介護連携課
            </p>

            <p>今年度の介護報酬改定および制度運用の変更点について、事業所・医療機関の実務担当者向けに説明会を実施します。</p>

            <p style=\"text-align: center;\">
                <span style=\"color: #dc2626; font-weight: bold; font-size: 1.125rem;\">【重要：事前アンケート回答へご協力のお願い】</span>
            </p>

            <h3>■ 説明会の主なトピック</h3>
            <ol>
                <li><strong>訪問看護・訪問リハビリテーションの改定ポイント</strong></li>
                <li><strong>医療・介護情報連携基盤（電子連絡帳システム）の運用開始について</strong></li>
                <li><strong>書式統一化に伴う提出書類の変更点</strong></li>
            </ol>

            <p style=\"text-align: center;\">
                <img src=\"https://picsum.photos/800/400\" alt=\"研修会会場レイアウト・受付案内\" />
            </p>

            <p><span style=\"text-decoration: underline wavy #dc2626 2px;\">※当日は受講票（または完了メール画面）を必ずご持参のうえ、15分前までに受付をお済ませください。</span></p>",

            // パターン3: 住民・地域医療系セミナー（表・テーブル表示、単位・受講条件）
            "<h1>認知症初期集中支援チーム 合同研修会</h1>
            <p>地域で認知症高齢者を支える多職種連携を推進するための実践的ワークショップです。</p>

            <h3>■ タイムスケジュールおよび内容</h3>
            <table border=\"1\" style=\"width: 100%; border-collapse: collapse;\">
                <thead>
                    <tr style=\"background-color: #f1f5f9;\">
                        <th style=\"padding: 8px;\">時間</th>
                        <th style=\"padding: 8px;\">内容</th>
                        <th style=\"padding: 8px;\">担当・講師</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style=\"padding: 8px; text-align: center;\">14:00〜14:15</td>
                        <td style=\"padding: 8px;\">開会・オリエンテーション</td>
                        <td style=\"padding: 8px;\">事務局</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 8px; text-align: center;\">14:15〜15:30</td>
                        <td style=\"padding: 8px;\">事例検討「初期対応における困難事例へのアプローチ」</td>
                        <td style=\"padding: 8px;\"><span style=\"color: #16a34a; font-weight: bold;\">みどり町診療所 チーム医師</span></td>
                    </tr>
                    <tr>
                        <td style=\"padding: 8px; text-align: center;\">15:30〜16:00</td>
                        <td style=\"padding: 8px;\">グループディスカッション・全体共有</td>
                        <td style=\"padding: 8px;\">参加者全員</td>
                    </tr>
                </tbody>
            </table>

            <h4>【問い合わせ・事前キャンセルについて】</h4>
            <p>体調不良等により当日辞退される場合は、必ず事務局（内線：2100）まで事前にご連絡くださいますようお願いいたします。</p>"
        ];

        // タイトル
        $titles = [
            '保険委員会講習会',
            '介護保険委員会研修会',
            '予防接種説明会',
            '公衆衛生委員会講習会',
            '事業説明会',
            'がん検診研修会',
            '地域医療研修会',
            '在宅医療研修会',
            '学術研修会',
            '胃がん検診研修会',
            '肺がん検診研修会',
            '認知症研修会',
        ];

        // 研修会の開始時間
        $start = $this->faker->dateTimeBetween('now', '+2 month');

        // 研修会の終了時間
        $end = (clone $start)->modify('+' . rand(1, 2) . ' hours');

        // 会議室
        $rooms = [
            '医師会館 4階講堂',
            'Zoom Web上',
            '医師会館 4階講堂（ハイブリッド開催）'
        ];

        // 医療機関名
        $hospitalNames = [
            'さくら医院',
            'ひまわりクリニック',
            'みどり町診療所',
            '東中央病院',
            'しらゆき病院',
            '南市立病院',
            '西上病院',
            '北下病院',
        ];

        return [
            'title' => $this->faker->randomElement($titles),
            'description' => $this->faker->randomElement($sampleDescriptions),
            'start_at' => $start,
            'end_at' => $end,
            'location' => $this->faker->randomElement($rooms),
            'lecture' => $this->faker->randomElement($hospitalNames) . ' ' . $this->faker->name(),
            'created_by' => User::whereHas('role', fn($q) => $q->where('name', 'staff'))
                ->inRandomOrder()
                ->value('id') ?? 1,
        ];
    }
}