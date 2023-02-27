-- -------------------------------------------------- --
-- アップロードテーブル                               --
-- -------------------------------------------------- --
CREATE TABLE equip_upload (
    work_date       int4,                               -- 運転日
    mac_no          int2,                               -- 機械番号
    siji_no         int4,                               -- 指示番号
    koutei          int2,                               -- 行程番号
    mac_state       char(1),                            -- 作業区分
                                                        -- int2 -> char(1) に変更
    from_time       int4,                               -- 開始時刻
    to_time         int4,                               -- 終了時刻
    cut_time        int4                                -- カット時間
);
CREATE INDEX equip_upload_idx01 ON equip_upload(work_date,mac_no,siji_no,koutei);
GRANT ALL ON equip_upload TO public;
