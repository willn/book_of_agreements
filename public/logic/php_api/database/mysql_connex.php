<?php
	#===============================
	# Database connection library
	# written in php to view MySQL databases
	# $Id: mysql_connex.php,v 1.12 2010-02-14 05:53:32 gocoho Exp $
	# GNU GPL, copylefted by Willie Northway, php@willienorthway.com
	#=================================

	if ( !extension_loaded( 'mysql' )) {
		if ( !dl( 'mysql.so' )) {
			exit( 'Cannot load mysql extension.' );
		}
	}

	require_once( $api_loc.'utils/errors.php' );

	#-----------------------------------------------------------
	# my_connect - used for connecting to a mysql database 
	#----------------------------------------------------------- 
	function my_connect( $G_DEBUG, $HDUP, $link='' )
	{
		$G_DEBUG = ErrRegister( $G_DEBUG, 'my_connect' );

		if ( !$link )
		{
			#default variable setting
			if ( !isset( $HDUP['host'] )) { $HDUP['host'] = 'localhost'; }
			if ( !isset( $HDUP['user'] )) { $HDUP['user'] = 'nobody'; }
			if ( !isset( $HDUP['password'] )) { $HDUP['password'] = ''; }

			$link = mysql_connect( $HDUP['host'], $HDUP['user'],
				$HDUP['password'] );
			if ( !$link || !mysql_select_db( $HDUP['database'], $link ))
			{ 
				array_push( $G_DEBUG, "H:$HDUP[host]", "U:$HDUP[user]" );

				if ( $G_DEBUG > 1 )
				{ array_push( $G_DEBUG, "P:".sha1( $HDUP[password] )); }

				if ( isset( $HDUP['database'] ))
				{
					array_push( $G_DEBUG, "D:$HDUP[database]",
						'error = '.mysql_error( ) );
				}

				ErrReport( "couldn't connect to the database",
					__FILE__, __LINE__, $G_DEBUG ); 
				return 0; 
			}
		}
		return $link;
	}

	#-----------------------------------------------------------
	# my_insert - used for inserting multiple items into a table
	#----------------------------------------------------------- 
	function my_insert( $G_DEBUG, $HDUP, $table, $Info, $link='' )
	{
		$G_DEBUG = ErrRegister( $G_DEBUG, 'my_insert' );
		$first_arg = 1;

		if ( !isset( $table ) || count( $Info ) < 1 )
		{
            array_push( $G_DEBUG, "table = $table", count( $Info ),
				'error = '.mysql_error( ) );
            ErrReport( "Must have all parameters to insert",
                __FILE__, __LINE__, $G_DEBUG );
            return 0;
		}


		if ( empty( $link )) 
		{ $link = my_connect( $G_DEBUG, $HDUP, $link ); }
		if ( empty( $link )) { return 0; }

		$sql = '';
		foreach($Info as $key=>$value) {
			if ( $first_arg ) { $first_arg = 0; }
			else { $sql .= ","; }

			#insert handling for unix_timestamp
			if ( is_string( $value ) && ( $value == 'unix_timestamp' ))
			{ $sql .= 'unix_timestamp( )'; }
			else { $sql .= "'".addslashes( $value )."'"; }
		}
		$sql = "INSERT INTO $table VALUES( $sql )";

		if ( $G_DEBUG[0] >= 3 ) { echo "<p>SQL: $sql</p>\n"; }
		$result = mysql_query( $sql );

		if ( !$result )
		{ 
			array_push( $G_DEBUG, "table = $table", "sql = $sql",
				'error = '.mysql_error( ) );
			ErrReport( "couldn't execute sql insert", __FILE__,
				__LINE__, $G_DEBUG ); 
		}
		return $result;

	}

    #-----------------------------------------------------------
    # my_update - used for updating multiple items into a table
    #-----------------------------------------------------------
    function my_update( $G_DEBUG, $HDUP, $table, $Info, $condition, $link='' )
    {
        $G_DEBUG = ErrRegister( $G_DEBUG, 'my_update' );
        $first_arg = 1;

        if ( !isset( $table ) || ( sizeof( $Info ) < 1 ))
        {
            array_push( $G_DEBUG, "table is empty",
                print_r( $Info, true ), "cond: $condition",
				'error = '.mysql_error( ) );
            ErrReport( "Must have all parameters to update",
                __FILE__, __LINE__, $G_DEBUG );
            return 0;
        }

		if ( empty( $link )) 
		{ $link = my_connect( $G_DEBUG, $HDUP, $link ); }
		if ( empty( $link )) { return 0; }

        $newvals = '';
        foreach ( $Info as $item )
        {
            if ( $first_arg ) { $first_arg = 0; }
            else { $newvals .= ","; }
            $newvals .= "$item";
        }

        $sql = "UPDATE $table SET $newvals $condition";
        if ( $G_DEBUG[0] >= 3 ) { echo "<p>SQL: $sql</p>\n"; }

		$result = mysql_query( $sql );

		if ( !$result )
		{ 
			array_push( $G_DEBUG, "table = $table", "sql = $sql",
				'error = '.mysql_error( ));
			ErrReport( "couldn't execute sql update", __FILE__,
				__LINE__, $G_DEBUG ); 
		}
		return $result;
    }


	#-------------------------------------------------------------
	# my_delete - delete an entry from the database
	#-------------------------------------------------------------
	function my_delete( $G_DEBUG, $HDUP, $col, $val, $link='' )
	{
		$G_DEBUG = ErrRegister( $G_DEBUG, 'my_delete' );
		$result = '';

		if ( !isset( $col ))
		{
			ErrReport( "can't delete without a column id",
				__FILE__, __LINE__, $G_DEBUG );
			return 0;
		}

		$sql = "DELETE FROM {$HDUP['table']} WHERE {$col}='{$val}'";
        if ( $G_DEBUG[0] >= 3 ) { echo "<p>SQL: $sql</p>\n"; }

		if ( empty( $link )) {
			$link = my_connect( $G_DEBUG, $HDUP, $link );
		}
		if ( empty( $link )) { return 0; }

		$result = mysql_query( $sql );

		if ( !$result )
		{ 
			array_push( $G_DEBUG, "sql = $sql", "user = $HDUP[user]",
				'error = '.mysql_error( ));
			ErrReport( "couldn't execute sql delete", __FILE__,
				__LINE__, $G_DEBUG ); 
		}
		return $result; 

	}

	#-------------------------------------------------------------
	# my_getInfo - retrieves a piece of data from a database
	#	- field is the info field desired for retrieval 
	#------------------------------------------------------------- 
	function my_getInfo( $G_DEBUG, $HDUP, $sql, $link='', $primary_key='' )
	{
		$G_DEBUG = ErrRegister( $G_DEBUG, 'my_getInfo' );
		$Found = array( );
		$num_found = 0;

		if ( $G_DEBUG[0] >= 3 ) { echo "<p>SQL: $sql</p>\n"; }

		if ( empty( $link )) 
		{ $link = my_connect( $G_DEBUG, $HDUP, $link ); }
		if ( empty( $link )) { return 0; }

		if ( $result = mysql_query( $sql, $link ))
		{
			if ( !empty( $primary_key ) > 0 )
			{
				while( $Info = mysql_fetch_array( $result, MYSQL_ASSOC ))
				{ $Found[$Info[$primary_key]] = $Info; }
			}
			else
			{
				while( $Info = mysql_fetch_array( $result, MYSQL_ASSOC ))
				{ array_push( $Found, $Info ); }
			}
		}
		else
		{
			if ( mysql_error( ) == 'Query was empty' ) { return ''; }

			array_push( $G_DEBUG, "H:$HDUP[host]", "D:$HDUP[database]", 
				"U:$HDUP[user]", "S:$sql", "L:$link", "R:$result",
				'error = '.mysql_error( ));
			if ( $G_DEBUG > 1 )
			{ array_push( $G_DEBUG, "P:".sha1( $HDUP['password'] )); }

			ErrReport( "no database query result", __FILE__,
				__LINE__, $G_DEBUG ); 
			return $result;
		}

		return $Found;
	}

	function my_lock( $G_DEBUG, $HDUP, $on_off, $link, $table='', $rw='' )
	{
		$G_DEBUG = ErrRegister( $G_DEBUG, 'my_lock' );

		if ( empty( $link )) 
		{ $link = my_connect( $G_DEBUG, $HDUP, $link ); }
		if ( empty( $link )) { return 0; }

		if ( $on_off ) { $sql = "lock tables $table $rw"; }
		else { $sql = 'unlock tables'; }

		if ( !( $result = mysql_query( $sql, $link )))
		{
			ErrReport( 'locking call malfunctioned', __FILE__,
				__LINE__, array( $G_DEBUG, $HDUP, $table, $rw,
				'error = '.mysql_error( )), $G_EMAIL); 
			return 0;
		}

		return 1;
	}

?>
