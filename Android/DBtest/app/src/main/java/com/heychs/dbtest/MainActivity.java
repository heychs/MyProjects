package com.heychs.dbtest;

import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

public class MainActivity extends AppCompatActivity {

    myDBHelper myHelper;
    EditText edtName, edtNumMembers, edtNameResult, edtNumMemberResult;
    Button btnInit, btnInsert, btnSelect;
    SQLiteDatabase sqlDB;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        setTitle("DBTest - 가수 그룹 관리");

        edtName = (EditText) findViewById(R.id.edtName);
        edtNumMembers = (EditText) findViewById(R.id.edtNumMembers);
        edtNameResult = (EditText) findViewById(R.id.edtNameResult);
        edtNumMemberResult = (EditText) findViewById(R.id.edtNumMembersResult);
        btnInit = (Button) findViewById(R.id.btnInit);
        btnInsert = (Button) findViewById(R.id.btnInsert);
        btnSelect = (Button) findViewById(R.id.btnSelect);

        myHelper  = new myDBHelper(this);
        btnInit.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View view) {
                sqlDB = myHelper.getWritableDatabase();
                myHelper.onUpgrade(sqlDB, 1, 2);
                sqlDB.close();
            }
        });

        btnInsert.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View view) {
                sqlDB = myHelper.getWritableDatabase();
                sqlDB.execSQL("INSERT INTO groupTBL VALUES ('"
                        + edtName.getText().toString() + "', "
                        + edtNumMembers.getText().toString() + ");");
                sqlDB.close();
                Toast.makeText(MainActivity.this, "입력됨", Toast.LENGTH_SHORT).show();
            }
        });

        btnSelect.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                sqlDB = myHelper.getWritableDatabase();
                Cursor cursor;
                cursor =  sqlDB.rawQuery("SELECT * FROM groupTBL;", null);

                String strNames = "그룹 이름" + "\r\n" + "-------" + "\r\n";
                String strNumMembers = "인원" + "\r\n" + "-------" + "\r\n";

                while (cursor.moveToNext()) {
                    strNames += cursor.getString(0) + "\r\n";
                    strNumMembers += cursor.getString(1) + "\r\n";
                }

                edtNameResult.setText(strNames);
                edtNumMemberResult.setText(strNumMembers);

                cursor.close();
                sqlDB.close();
            }
        });

    }

}
