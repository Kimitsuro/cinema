/*==============================================================*/
/* DBMS name:      PostgreSQL 9.x                               */
/* Created on:     02.06.2025 6:13:30                           */
/*==============================================================*/


drop index Admin_PK;

drop table Admin;

drop index manage_FK;

drop index Employee_PK;

drop table Employee;

drop index Goer_PK;

drop table Goer;

drop index Hall_PK;

drop table Hall;

drop index add_FK;

drop index Movie_PK;

drop table Movie;

drop index idx_purchase_date;

drop index does_FK;

drop index have_FK;

drop index Purchase_PK;

drop table Purchase;

drop index include_FK;

drop index commits_FK;

drop index Refund_PK;

drop table Refund;

drop index occupy_FK;

drop index contains_FK;

drop index Seat_PK;

drop table Seat;

drop index idx_session_data;

drop index show_FK;

drop index pass_FK;

drop index change_FK;

drop index Session_PK;

drop table Session;

drop index idx_ticket_status;

drop index has_FK;

drop index include2_FK;

drop index occupy2_FK;

drop index treat_FK;

drop index Ticket_PK;

drop table Ticket;

/*==============================================================*/
/* Table: Admin                                                 */
/*==============================================================*/
create table Admin (
   admin_login          VARCHAR(50)          not null,
   admin_password       VARCHAR(255)         not null,
   admin_name           VARCHAR(100)         not null,
   constraint PK_ADMIN primary key (admin_login)
);

/*==============================================================*/
/* Index: Admin_PK                                              */
/*==============================================================*/
create unique index Admin_PK on Admin (
admin_login
);

/*==============================================================*/
/* Table: Employee                                              */
/*==============================================================*/
create table Employee (
   employee_login       VARCHAR(50)          not null,
   admin_login          VARCHAR(50)          not null,
   employee_password    VARCHAR(255)         not null,
   employee_name        VARCHAR(100)         not null,
   employee_position    VARCHAR(200)         not null,
   constraint PK_EMPLOYEE primary key (employee_login)
);

/*==============================================================*/
/* Index: Employee_PK                                           */
/*==============================================================*/
create unique index Employee_PK on Employee (
employee_login
);

/*==============================================================*/
/* Index: manage_FK                                             */
/*==============================================================*/
create  index manage_FK on Employee (
admin_login
);

/*==============================================================*/
/* Table: Goer                                                  */
/*==============================================================*/
create table Goer (
   goer_email           VARCHAR(320)         not null,
   goer_name            VARCHAR(100)         not null,
   goer_phone           CHAR(10)             null,
   goer_date            DATE                 not null,
   goer_password        VARCHAR(255)         not null,
   constraint PK_GOER primary key (goer_email)
);

/*==============================================================*/
/* Index: Goer_PK                                               */
/*==============================================================*/
create unique index Goer_PK on Goer (
goer_email
);

/*==============================================================*/
/* Table: Hall                                                  */
/*==============================================================*/
create table Hall (
   hall_id              SERIAL               not null,
   hall_capacity        INT2                 not null,
   hall_type            VARCHAR(20)          not null,
   constraint PK_HALL primary key (hall_id)
);

/*==============================================================*/
/* Index: Hall_PK                                               */
/*==============================================================*/
create unique index Hall_PK on Hall (
hall_id
);

/*==============================================================*/
/* Table: Movie                                                 */
/*==============================================================*/
create table Movie (
   movie_id             SERIAL               not null,
   admin_login          VARCHAR(50)          not null,
   movie_title          VARCHAR(100)         not null,
   movie_genre          VARCHAR(50)          not null,
   movie_duration       INT2                 not null,
   movie_format         VARCHAR(100)         not null,
   movie_age            INT2                 not null,
   movie_poster         VARCHAR(255)         not null,
   movie_description    TEXT                 not null,
   movie_status         VARCHAR(50)          not null,
   constraint PK_MOVIE primary key (movie_id)
);

/*==============================================================*/
/* Index: Movie_PK                                              */
/*==============================================================*/
create unique index Movie_PK on Movie (
movie_id
);

/*==============================================================*/
/* Index: add_FK                                                */
/*==============================================================*/
create  index add_FK on Movie (
admin_login
);

/*==============================================================*/
/* Table: Purchase                                              */
/*==============================================================*/
create table Purchase (
   purchase_id          SERIAL               not null,
   session_id           INT4                 not null,
   goer_email           VARCHAR(320)         not null,
   purchase_date        DATE                 not null,
   constraint PK_PURCHASE primary key (purchase_id)
);

/*==============================================================*/
/* Index: Purchase_PK                                           */
/*==============================================================*/
create unique index Purchase_PK on Purchase (
purchase_id
);

/*==============================================================*/
/* Index: have_FK                                               */
/*==============================================================*/
create  index have_FK on Purchase (
session_id
);

/*==============================================================*/
/* Index: does_FK                                               */
/*==============================================================*/
create  index does_FK on Purchase (
goer_email
);

/*==============================================================*/
/* Index: idx_purchase_date                                     */
/*==============================================================*/
create  index idx_purchase_date on Purchase (
purchase_date
);

/*==============================================================*/
/* Table: Refund                                                */
/*==============================================================*/
create table Refund (
   refund_id            SERIAL               not null,
   goer_email           VARCHAR(320)         not null,
   ticket_id            INT4                 not null,
   refund_date          DATE                 not null,
   refund_reason        VARCHAR(500)         not null,
   refund_status        VARCHAR(50)          not null,
   constraint PK_REFUND primary key (refund_id)
);

/*==============================================================*/
/* Index: Refund_PK                                             */
/*==============================================================*/
create unique index Refund_PK on Refund (
refund_id
);

/*==============================================================*/
/* Index: commits_FK                                            */
/*==============================================================*/
create  index commits_FK on Refund (
goer_email
);

/*==============================================================*/
/* Index: include_FK                                            */
/*==============================================================*/
create  index include_FK on Refund (
ticket_id
);

/*==============================================================*/
/* Table: Seat                                                  */
/*==============================================================*/
create table Seat (
   seat_id              SERIAL               not null,
   hall_id              INT4                 not null,
   ticket_id            INT4                 null,
   sear_number          INT2                 not null,
   seat_row             INT2                 not null,
   seat_type            VARCHAR(50)          not null,
   constraint PK_SEAT primary key (seat_id)
);

/*==============================================================*/
/* Index: Seat_PK                                               */
/*==============================================================*/
create unique index Seat_PK on Seat (
seat_id
);

/*==============================================================*/
/* Index: contains_FK                                           */
/*==============================================================*/
create  index contains_FK on Seat (
hall_id
);

/*==============================================================*/
/* Index: occupy_FK                                             */
/*==============================================================*/
create  index occupy_FK on Seat (
ticket_id
);

/*==============================================================*/
/* Table: Session                                               */
/*==============================================================*/
create table Session (
   session_id           SERIAL               not null,
   employee_login       VARCHAR(50)          null,
   hall_id              INT4                 not null,
   movie_id             INT4                 not null,
   session_data         DATE                 not null,
   session_price        DECIMAL(10,2)        not null,
   constraint PK_SESSION primary key (session_id)
);

/*==============================================================*/
/* Index: Session_PK                                            */
/*==============================================================*/
create unique index Session_PK on Session (
session_id
);

/*==============================================================*/
/* Index: change_FK                                             */
/*==============================================================*/
create  index change_FK on Session (
employee_login
);

/*==============================================================*/
/* Index: pass_FK                                               */
/*==============================================================*/
create  index pass_FK on Session (
hall_id
);

/*==============================================================*/
/* Index: show_FK                                               */
/*==============================================================*/
create  index show_FK on Session (
movie_id
);

/*==============================================================*/
/* Index: idx_session_data                                      */
/*==============================================================*/
create  index idx_session_data on Session (
session_data
);

/*==============================================================*/
/* Table: Ticket                                                */
/*==============================================================*/
create table Ticket (
   ticket_id            SERIAL               not null,
   session_id           INT4                 not null,
   seat_id              INT4                 not null,
   refund_id            INT4                 null,
   purchase_id          INT4                 not null,
   ticket_status        VARCHAR(100)         not null,
   constraint PK_TICKET primary key (ticket_id)
);

/*==============================================================*/
/* Index: Ticket_PK                                             */
/*==============================================================*/
create unique index Ticket_PK on Ticket (
ticket_id
);

/*==============================================================*/
/* Index: treat_FK                                              */
/*==============================================================*/
create  index treat_FK on Ticket (
session_id
);

/*==============================================================*/
/* Index: occupy2_FK                                            */
/*==============================================================*/
create  index occupy2_FK on Ticket (
seat_id
);

/*==============================================================*/
/* Index: include2_FK                                           */
/*==============================================================*/
create  index include2_FK on Ticket (
refund_id
);

/*==============================================================*/
/* Index: has_FK                                                */
/*==============================================================*/
create  index has_FK on Ticket (
purchase_id
);

/*==============================================================*/
/* Index: idx_ticket_status                                     */
/*==============================================================*/
create  index idx_ticket_status on Ticket (
ticket_status
);

alter table Employee
   add constraint FK_EMPLOYEE_MANAGE_ADMIN foreign key (admin_login)
      references Admin (admin_login)
      on delete restrict on update restrict;

alter table Movie
   add constraint FK_MOVIE_ADD_ADMIN foreign key (admin_login)
      references Admin (admin_login)
      on delete restrict on update restrict;

alter table Purchase
   add constraint FK_PURCHASE_DOES_GOER foreign key (goer_email)
      references Goer (goer_email)
      on delete restrict on update restrict;

alter table Purchase
   add constraint FK_PURCHASE_HAVE_SESSION foreign key (session_id)
      references Session (session_id)
      on delete restrict on update restrict;

alter table Refund
   add constraint FK_REFUND_COMMITS_GOER foreign key (goer_email)
      references Goer (goer_email)
      on delete restrict on update restrict;

alter table Refund
   add constraint FK_REFUND_INCLUDE_TICKET foreign key (ticket_id)
      references Ticket (ticket_id)
      on delete restrict on update restrict;

alter table Seat
   add constraint FK_SEAT_CONTAINS_HALL foreign key (hall_id)
      references Hall (hall_id)
      on delete restrict on update restrict;

alter table Seat
   add constraint FK_SEAT_OCCUPY_TICKET foreign key (ticket_id)
      references Ticket (ticket_id)
      on delete restrict on update restrict;

alter table Session
   add constraint FK_SESSION_CHANGE_EMPLOYEE foreign key (employee_login)
      references Employee (employee_login)
      on delete restrict on update restrict;

alter table Session
   add constraint FK_SESSION_PASS_HALL foreign key (hall_id)
      references Hall (hall_id)
      on delete restrict on update restrict;

alter table Session
   add constraint FK_SESSION_SHOW_MOVIE foreign key (movie_id)
      references Movie (movie_id)
      on delete restrict on update restrict;

alter table Ticket
   add constraint FK_TICKET_HAS_PURCHASE foreign key (purchase_id)
      references Purchase (purchase_id)
      on delete restrict on update restrict;

alter table Ticket
   add constraint FK_TICKET_INCLUDE2_REFUND foreign key (refund_id)
      references Refund (refund_id)
      on delete restrict on update restrict;

alter table Ticket
   add constraint FK_TICKET_OCCUPY2_SEAT foreign key (seat_id)
      references Seat (seat_id)
      on delete restrict on update restrict;

alter table Ticket
   add constraint FK_TICKET_TREAT_SESSION foreign key (session_id)
      references Session (session_id)
      on delete restrict on update restrict;

