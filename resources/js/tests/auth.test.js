import { describe, it, expect } from "vitest";

// ログイン後やヘッダーで使う「ロール名の正規化関数」の判定テスト
const normalizeRoleName = (value) => {
    if (!value) return null;
    if (typeof value === "string") {
        return value.toLowerCase().replace(/[-\s]+/g, "_");
    }
    if (typeof value === "object" && value !== null) {
        const name = value.name || value.role_name || value.roleName;
        return name ? normalizeRoleName(name) : null;
    }
    return null;
};

// admin / staff 判定ロジック
const isAdminOrStaff = (role) => {
    const normalized = normalizeRoleName(role);
    return normalized === "admin" || normalized === "staff" || normalized === "system_admin";
};

describe("認証・権限判定のテスト", () => {
    it('DBからのオブジェクト形式 { id: 1, name: "admin" } で admin と判定できること', () => {
        const role = { id: 1, name: "admin" };
        expect(normalizeRoleName(role)).toBe("admin");
        expect(isAdminOrStaff(role)).toBe(true);
    });

    it('文字列 "admin" でも admin と判定できること', () => {
        expect(isAdminOrStaff("admin")).toBe(true);
    });

    it("一般ユーザー (member) は admin/staff ではないと判定されること", () => {
        const role = { id: 4, name: "member" };
        expect(isAdminOrStaff(role)).toBe(false);
    });

    it("未ログイン (null / undefined) のときにエラーにならず false を返すこと", () => {
        expect(isAdminOrStaff(null)).toBe(false);
        expect(isAdminOrStaff(undefined)).toBe(false);
    });
});