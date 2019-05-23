 -- 国連による世界地理区分
drop table if exists tmp_un_m49;
create table tmp_un_m49 (
    region_name text not null,
    sub_region_name text not null,
    iso_alpha3 char(3) unique not null
);

-- 国名コードの標準
drop table if exists tmp_iso3166;
create table tmp_iso3166 (
    country_name text not null,
    country_code char(2) unique not null,
    iso_alpha3 char(3) unique not null
);

-- 地域
drop table if exists region;
create table region (
    id bigint not null auto_increment,
    name text not null,
    primary key (id)
);

-- 国
drop table if exists country;
create table country (
    id bigint not null auto_increment,
    name text not null,
    code char(2) unique not null,
    region_id bigint not null,
    primary key (id)
);

-- 観測点
drop table if exists city;
create table city (
    id bigint unique not null,
    name text not null,
    country_code char(2) not null,
    coord_lon double not null,
    coord_lat double not null,
    primary key (id)
);

-- 観測点の天気
drop table if exists weather;
create table weather (
    id bigint not null auto_increment,
    city_id bigint not null,
    branch bigint not null,
    weather_text text,
    weather_icon text,
    temperature float,
    temperature_max float,
    temperature_min float,
    wind_speed float,
    wind_degree float,
    forecast_at datetime not null,
    created_at datetime not null,
    updated_at datetime not null,
    primary key (id)
);

-- リレーション
create index region_id_idx ON country (region_id);
create index county_code_idx ON city (country_code);
create index weather_city_idx ON weather (city_id);

-- 観測点一覧
create fulltext index city_name_idx ON city (name(255));