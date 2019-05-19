drop table if exists tmp_un_m49;
create table tmp_un_m49 (
    region_name text not null,
    sub_region_name text not null,
    iso_alpha3 char(3) unique not null
);

drop table if exists tmp_iso3166;
create table tmp_iso3166 (
    country_name text not null,
    country_code char(2) unique not null,
    iso_alpha3 char(3) unique not null
);

drop table if exists region;
create table region (
    id bigint not null auto_increment,
    name text not null,
    primary key (id)
);

drop table if exists country;
create table country (
    id bigint not null auto_increment,
    name text not null,
    code char(2) unique not null,
    region_id bigint not null,
    primary key (id)
);

drop table if exists city;
create table city (
    id bigint unique not null,
    name text not null,
    country_code char(2) not null,
    coord_lon double not null,
    coord_lat double not null,
    primary key (id)
);

create index region_id_idx ON country (region_id);
create index county_code_idx ON city (country_code);
