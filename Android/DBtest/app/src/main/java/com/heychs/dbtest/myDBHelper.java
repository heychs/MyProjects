package com.heychs.dbtest;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

/**
 * Created by Hwansuk Choi <heychs@gmail.com> on 2016-11-11.
 */

public class myDBHelper extends SQLiteOpenHelper {
    public myDBHelper(Context context){
        super(context, "groupDB", null, 1);
    }

    @Override
    public void onCreate(SQLiteDatabase sqLiteDatabase) {
        sqLiteDatabase.execSQL("CREATE TABLE groupTBL (gName CHAR(20) PRIMARY KEY, gNumber INTEGER);");
    }

    @Override
    public void onUpgrade(SQLiteDatabase sqLiteDatabase, int i, int i1) {
        sqLiteDatabase.execSQL("DROP TABLE IF EXISTS groupTBL;");
        onCreate(sqLiteDatabase);
    }
}
