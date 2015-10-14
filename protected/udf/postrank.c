   #ifdef STANDARD
   /* STANDARD is defined, don't use any mysql functions */
   #include <stdlib.h>
   #include <stdio.h>
   #include <string.h>
   #ifdef __WIN__
   typedef unsigned __int64 ulonglong;/* Microsofts 64 bit types */
   typedef __int64 longlong;
   #else
   typedef unsigned long long ulonglong;
   typedef long long longlong;
   #endif /*__WIN__*/
   #else
   #include <my_global.h>
   #include <my_sys.h>
   #if defined(MYSQL_SERVER)
   #include <m_string.h>/* To get strmov() */
   #else
   /* when compiled as standalone */
   #include <string.h>
   #define strmov(a,b) stpcpy(a,b)
   #define bzero(a,b) memset(a,0,b)
   #define memcpy_fixed(a,b,c) memcpy(a,b,c)
   #endif
   #endif
   #include <mysql.h>
   #include <ctype.h>
   
   #ifdef HAVE_DLOPEN
   
   #if !defined(HAVE_GETHOSTBYADDR_R) || !defined(HAVE_SOLARIS_STYLE_GETHOST)
   static pthread_mutex_t LOCK_hostname;
   #endif
   
   #include <math.h>
   
   my_bool postrank_init(UDF_INIT *initid, UDF_ARGS *args, char *message);
   void postrank_deinit(UDF_INIT *initid __attribute__((unused)));
   double postrank(UDF_INIT* initid, UDF_ARGS* args __attribute__((unused)),
                     char* is_null __attribute__((unused)), char* error __attribute__((unused)));
   
   my_bool postrank_init(UDF_INIT *initid, UDF_ARGS *args, char *message)
   {
     if(!(args->arg_count == 3)) {
       strcpy(message, "Expected three arguments");
       return 1;
     }
   
     args->arg_type[0] = REAL_RESULT;
     args->arg_type[1] = REAL_RESULT;
     args->arg_type[2] = REAL_RESULT;
   
     return 0;
   }
   
   void postrank_deinit(UDF_INIT *initid __attribute__((unused)))
   {
   
   }
   
   double postrank(UDF_INIT* initid, UDF_ARGS* args __attribute__((unused)),
                     char* is_null __attribute__((unused)), char* error __attribute__((unused)))
   {
     double ups = *((double *)(args->args[0]));
     double downs = *((double *)(args->args[1]));
     double d = *((double *)(args->args[2]));

       double final = 0.00;
       double my_sign = 1.0;

       if(ups - downs > 0){
		my_sign = 1.0;
       }else if(ups - downs < 0){
		my_sign = -1.0;
       }else{
		my_sign = 0.0;
       }

       int myups = (int) ups;
       int mydowns = (int) downs;

       int my_max = 1;
       if(abs(myups-mydowns) > 1){
		my_max = abs(myups-mydowns);
       }

	double dbmy_max = (double) my_max;

	double result = log10(dbmy_max) * my_sign + ((d - 1444000000.0) / 64800.0);

	final = round( 10000000.0 * result ) / 10000000.0;

	return final;
   }

   #endif /* HAVE_DLOPEN */

