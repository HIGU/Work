-- -------------------------------------------------- --
-- 端材管理マスタ                                     --
-- -------------------------------------------------- --
create table equip_abandonment_item (
    item_code   char(7),                -- 材料コード
    length      numeric,                -- 端材の長さ(M)
    weight      numeric                 -- 端材の重さ(kg)
);
create index equip_abandonment_item_idx01 on equip_abandonment_item(item_code);
grant all on equip_abandonment_item to public;

