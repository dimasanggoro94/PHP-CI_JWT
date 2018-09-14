# PHP-CI_JWT
Code Igniter JWT Authorization

-- Run composer

~ Login = URL.../PHP-CI_JWT/rest/generate
	>POST
 	- Headers
	Content-Type:application/x-www-form-urlencoded
	- Body
	username:admin
	password:admin+

~ GeProduct = URL.../PHP-CI_JWT/api/produk
	>GET
	- Headers
	Content-Type:application/x-www-form-urlencoded
	Authorization:ResponseTokenFromLogin



-- SQL Patch

DROP TABLE IF EXISTS `produk`;
CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `deskripsi` varchar(100) NOT NULL,
  `harga` varchar(100) NOT NULL,
  `gambar` varchar(100) NOT NULL,
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `produk` (`id_produk`, `nama`, `deskripsi`, `harga`, `gambar`) VALUES
(1,	'aaa',	'aaaaa',	'1',	''),
(2,	'bbb',	'bbbb',	'2',	'');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` (`id_user`, `username`, `password`, `email`) VALUES
(1,	'admin',	'admin+',	'');
