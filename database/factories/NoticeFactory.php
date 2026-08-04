<?php

namespace Database\Factories;

use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 📢 お知らせ・通知用のリッチHTML本文パターン（3種類）
        $sampleBodies = [
            // パターン1: 重要なお知らせ（システムメンテ・注意喚起・波線下線・長文URL・引用）
            '<h1>【重要】令和8年度 システムリニューアルおよび一時停止のお知らせ</h1>
            <p>平素より大変お世話になっております。システム管理部より、次期システムへの移行に伴う計画停電・サービス停止についてご案内いたします。</p>

            <h2>1. サービス停止日時</h2>
            <ul>
                <li><strong>開始日時：</strong>2026年9月20日(日) 22:00</li>
                <li><strong>終了予定：</strong>2026年9月21日(月) 08:00（※作業状況により前後する場合があります）</li>
            </ul>

            <h2>2. 変更点および注意事項</h2>
            <p>以下の変更点につきまして、あらかじめご確認をお願いいたします。</p>
            <ul>
                <li>ログインURLの変更（新ポータルサイトへ自動転送されます）</li>
                <li>デザインの刷新による操作性の向上</li>
                <li><span style="text-decoration: underline wavy #dc2626 2px;">セキュリティ強化のため、初回ログイン時にパスワード再設定が必要です。</span></li>
            </ul>

            <h3>■ 関連マニュアル・詳細URL</h3>
            <p>事前マニュアルは下記URLよりアクセスしてご確認ください。</p>
            <p><a href="https://example.com/share/committee/public-relations/2026/meetings/03-report-document-draft-v2-check-page-test-long-url-path">https://example.com/share/committee/public-relations/2026/meetings/03-report-document-draft-v2-check-page-test-long-url-path"</a></p>

            <blockquote>
                <p>※注意: メンテナンス期間中はすべてのWeb手続き・データ参照ができなくなります。ご不便をおかけしますがご容赦ください。</p>
            </blockquote>',

            // パターン2: イベント・研修会のご案内（中央揃え・右揃え・文字色・画像・番号リスト）
            '<h2>秋季研修会および意見交換会 開催のご案内</h2>
            <p style="text-align: right; color: #4b5563;">
                発信元：研修運営委員会<br>
                更新日：2026年8月1日
            </p>

            <p>今年度の秋季研修会を以下の通り開催いたします。皆さまの奮ってのご参加をお待ちしております。</p>

            <p style="text-align: center;">
                <span style="color: #dc2626; font-weight: bold; font-size: 1.125rem;">【参加事前申込締切：2026年10月15日(金)まで】</span>
            </p>

            <p style="text-align: center;">
                <img src="https://picsum.photos/800/400" alt="研修会メインビジュアル" />
            </p>

            <h3>■ 開催概要</h3>
            <ol>
                <li><strong>日時：</strong> 2026年10月28日(水) 14:00〜16:00</li>
                <li><strong>会場：</strong> 本部大ホール（オンラインZoom同時配信あり）</li>
                <li><strong>対象者：</strong> 全会員および関連スタッフ</li>
            </ol>

            <p style="text-align: right; color: #4b5563;">
                お問合せ窓口：<a href="mailto:info@example.com">info@example.com</a>
            </p>',

            // パターン3: 規程改正・手続き方法の変更（テーブル・コードブロック）
            '<h1>【通知】事務手続きフォーマットおよび提出期限の改定について</h1>
            <p>事務局より、今年度後半からの申請手続きに関する改定事項をお知らせいたします。</p>

            <h3>■ 主な改定点一覧</h3>
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f1f5f9;">
                        <th style="padding: 8px;">申請種別</th>
                        <th style="padding: 8px;">従来の運用</th>
                        <th style="padding: 8px;">変更後の運用</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 8px; text-align: center;">月次報告書</td>
                        <td style="padding: 8px;">毎月末日・紙提出</td>
                        <td style="padding: 8px;"><span style="color: #2563eb; font-weight: bold;">翌月5日まで・Webフォーム</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; text-align: center;">経費精算</td>
                        <td style="padding: 8px;">都度持ち込み</td>
                        <td style="padding: 8px;"><span style="color: #16a34a; font-weight: bold;">オンライン一括申請</span></td>
                    </tr>
                </tbody>
            </table>

            <h4>【開発・システム連携用識別コード】</h4>
            <pre style="background-color: #0f172a; color: #f8fafc; padding: 12px; border-radius: 8px; overflow-x: auto;"><code>NOTICE_TYPE: SYSTEM_REVISION
                REVISION_CODE: REV-2026-09
                EFFECTIVE_DATE: 2026-10-01</code></pre>'
        ];

        return [
            'title' => $this->faker->randomElement([
                '【重要】令和8年度 システムリニューアルおよび一時停止のお知らせ',
                '【ご案内】2026年度 秋季研修会および意見交換会の開催について',
                '【通知】事務手続きフォーマットおよび提出期限の改定について',
            ]),
            'committee_name' => $this->faker->randomElement([
                'システム管理部',
                '研修運営委員会',
                '総務・企画委員会',
                '事務局',
            ]),
            'body' => $this->faker->randomElement($sampleBodies),
            'category_id' => NoticeCategory::inRandomOrder()->value('id') ?? 1,
            'published_at' => now()->subDays(rand(1, 30)),
            'created_by' => User::whereHas('role', fn($q) => $q->where('name', 'staff'))
                ->inRandomOrder()->value('id') ?? 1,
        ];
    }
}