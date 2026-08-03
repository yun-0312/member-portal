<?php

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ContentCategory;
use App\Models\User;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 📄 委員会報告・会議議事録・各種資料確認用のリッチHTML本文パターン（3種類）
        $sampleBodies = [
            // パターン1: 標準的な委員会開催報告（基本構成、見出し、波線下線、長文URL）
            '<h1>第3回 広報委員会 開催報告および議事録</h1>
            <p>令和8年度 第3回広報委員会を以下の通り開催いたしましたので報告いたします。</p>

            <h2>1. 開催概要</h2>
            <ul>
                <li><strong>日時：</strong>2026年7月15日(水) 15:00〜16:30</li>
                <li><strong>場所：</strong>本部第2会議室（Web併用）</li>
                <li><strong>出席者：</strong>山田委員長、佐藤委員、鈴木委員、高橋委員（事務局）</li>
            </ul>

            <h2>2. 審議・報告事項</h2>
            <h3>（1）広報誌「さくら」秋号の編集スケジュールについて</h3>
            <p>編集案について協議を行い、次回9月発行分についての特集企画（地域連携事業の取り組み）が決定いたしました。</p>
            <p><span style="text-decoration: underline wavy #dc2626 2px;">※原稿の最終締め切りは8月20日(木)必着となります。</span></p>

            <h3>（2）公式Webサイトのリニューアル進行状況</h3>
            <p>制作会社より提示されたトップページデザイン案の確認を行いました。提出資料の詳細は下記共有フォルダURLより各自ご確認ください。</p>
            <p><a href="https://example.com/share/committee/public-relations/2026/meetings/03-report-document-draft-v2-check-page-test-long-url-path">https://example.com/share/committee/public-relations/2026/meetings/03-report-document-draft-v2-check-page-test-long-url-path</a></p>

            <blockquote>
                <p><strong>【決定事項】</strong>次回「第4回広報委員会」は 2026年9月10日(木) 14:00〜 オンラインにて開催予定です。</p>
            </blockquote>',

            // パターン2: 研修事業実行委員会報告（中央・右揃え、文字色、強調表示、番号リスト）
            '<h2>令和8年度 夏季合同研修会 実行委員会 会議議事録</h2>
            <p style="text-align: right; color: #4b5563;">
                作成日：2026年6月30日<br>
                記録者：企画総務課
            </p>

            <p>今年度開催予定の夏季合同研修会に向けた「第2回 実行委員会」の協議結果をお知らせします。</p>

            <p style="text-align: center;">
                <span style="color: #2563eb; font-weight: bold; font-size: 1.125rem;">【メインテーマ：地域社会におけるデジタル活用の現状と課題】</span>
            </p>

            <h3>■ 各班からの報告事項</h3>
            <ol>
                <li><strong>会場・設営班：</strong>メイン会場（大ホール）および分科会用小部屋の確保完了。</li>
                <li><strong>講師対応班：</strong>基調講演の外部講師2名より登壇承諾書を受領済み。</li>
                <li><strong>広報・受付班：</strong>次回7月上旬より参加申し込みフォームを開設予定。</li>
            </ol>

            <p style="text-align: center;">
                <img src="https://picsum.photos/800/400" alt="会場レイアウト案" />
            </p>

            <p><span style="color: #dc2626; font-weight: bold;">【次回までの宿題】</span>各委員は担当部署にて分科会ワークショップの進行シナリオ案を作成し、7月10日までに事務局へ提出してください。</p>',

            // パターン3: 理事会・常任委員会報告（表・テーブル、注釈、コード/データ形式表示）
            '<h1>第2回 理事会 審議結果報告</h1>
            <p>2026年5月20日に開催された第2回理事会における、予算配分および事業計画の審議結果一覧です。</p>

            <h3>■ 審議議題と採決結果</h3>
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f1f5f9;">
                        <th style="padding: 8px;">議案番号</th>
                        <th style="padding: 8px;">議題名</th>
                        <th style="padding: 8px;">審議結果</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 8px; text-align: center;">第1号議案</td>
                        <td style="padding: 8px;">令和7年度 事業報告および決算承認の件</td>
                        <td style="padding: 8px; text-align: center;"><span style="color: #16a34a; font-weight: bold;">原案通り可決</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; text-align: center;">第2号議案</td>
                        <td style="padding: 8px;">特別委員会設置に関する規程改正の件</td>
                        <td style="padding: 8px; text-align: center;"><span style="color: #16a34a; font-weight: bold;">原案通り可決</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; text-align: center;">第3号議案</td>
                        <td style="padding: 8px;">新システム導入に伴う予算補正の件</td>
                        <td style="padding: 8px; text-align: center;"><span style="color: #2563eb; font-weight: bold;">一部修正の上可決</span></td>
                    </tr>
                </tbody>
            </table>

            <h4>【補足データ：承認された予算補正ID一覧】</h4>
            <pre style="background-color: #0f172a; color: #f8fafc; padding: 12px; border-radius: 8px; overflow-x: auto;"><code>BOARD_MEETING_REF: 2026-BM02
APPROVED_BUDGET_CODE: SEC-2026-08A
STATUS: CONFIRMED</code></pre>'
        ];

        return [
            // ランダムでカテゴリ・ユーザーIDを取得
            'category_id'   => ContentCategory::inRandomOrder()->first()?->id ?? 1,
            'subcategory_id' => null,
            'title'         => $this->faker->randomElement([
                '【議事録】令和8年度 第3回広報委員会 開催報告',
                '【報告】夏季合同研修会 実行委員会（第2回）協議結果',
                '【理事会報告】第2回 理事会 審議結果および決定事項一覧',
            ]),
            'body'          => $this->faker->randomElement($sampleBodies),
            'meeting_date'   => $this->faker->dateTimeBetween('-1 month', 'now'),
            'published_at'   => $this->faker->dateTimeBetween('-3 months', 'now'),
            'created_by'     => User::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}