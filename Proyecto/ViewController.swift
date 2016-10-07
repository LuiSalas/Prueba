//
//  ViewController.swift
//  Proyecto
//
//  Created by Luis Salas Rodriguez on 7/10/16.
//  Copyright © 2016 Luis Salas Rodriguez. All rights reserved.
//

import UIKit
import Alamofire
import SwiftyJSON

class ViewController: UIViewController {

    override func viewDidLoad() {
        super.viewDidLoad()
        // Do any additional setup after loading the view, typically from a nib.
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }

    
    /*
    func numberOfVowelsInString(string: String) -> Int
    {
        let vowels: [Character] = ["a","e","i","o","u","A","E","I","O","U"]
        var numberOfVowels = 0
        for character in string.characters {
            if vowels.contains(character)
            {
                numberOfVowels += 1
            }
        }
        return numberOfVowels
    }
   */
    
    
    /*
    //Post, loggin
    @IBAction func pla(_ sender: UIButton) {
        
        var url: String = "http://52.89.227.55/api/"
        Alamofire.request("http://52.89.227.55/api/"
            + "authenticate?user=admin&password=secret"
            , method: .post).validate(statusCode: 200..<300)
            .validate(contentType: ["application/json"])
            .responseData { response in
                switch response.result {
                case .success:
                    print("Validation Successful")
                    let json = JSON(data: response.data!)
                    if let value = response.result.value {
                        let todo = JSON(value)
                        print("The todo is: " + todo.description)
                    }
                    
                    
                    //Imprisión de datos
                    print("JSON:    \n",json.description)
                    print("Token:",json["token"].stringValue)
                    
                    self.lave2.text = json["token"].stringValue
                    
                    print("User:",json["Usuario"].stringValue)
                    self.txtView.text = "Token: "+json["token"].stringValue + "\n  Usuario: "+json["Usuario"].stringValue
                    
                    print("Id Especialidad :",json["IdEspecialidad"].int32Value)
                    print("Id Ciclo academico:",json["IdCicloAcademico"].int32Value)
                    
                case .failure(let error):
                    print("Error")
                    
                }
                
                return
        }
        
    }
    
    
    
    //Método, Get para ver facultades
    @IBAction func desPre(_ sender: AnyObject) {
        
        var title:String = lave2.text!
        let headers: HTTPHeaders = [
            "Authorization": "Bearer "+title]
        Alamofire.request("http://52.89.227.55/api/"+"faculties?since=1463183832",headers: headers).responseJSON { response in
            switch response.result {
            case .success:
                print("Validation Successful")
                let json = JSON(data: response.data!)
                if let value = response.result.value {
                    let todo = JSON(value)
                    print("The todo is: " + todo.description)
                    // self.txtView2.text=todo.description
                    
                }
                //Imprimir datos
                
                var i = 1
                for (_,faculty):(String, JSON) in json {
                    print(i,"------------------------------------------")
                    print("Id",faculty["IdEspecialidad"].int32Value)
                    print("Nombre",faculty["Nombre"].stringValue)
                    print("Descripcion",faculty["Descripcion"].stringValue)
                    print("Codigo",faculty["Codigo"].stringValue)
                    i = i+1
                    
                    self.txtView2.text.append(faculty["Nombre"].stringValue)
                    self.txtView2.text.append("\n")
                    self.txtView2.text.append(faculty["Descripcion"].stringValue)
                    self.txtView2.text.append("\n")
                    self.txtView2.text.append("\n")
                }
                
                
            case .failure(let error):
                print("Error")
                
            }
            
        }
    }
    
    
    
    
    //Pruebas con apiari
    @IBAction func Apiari(_ sender: AnyObject) {
        
        Alamofire.request("https://private-2595c-getmeallmyreviews13.apiary-mock.com/question2").responseJSON { response in
            switch response.result {
            case .success:
                print("Validation Successful")
                let json = JSON(data: response.data!)
                if let value = response.result.value {
                    let todo = JSON(value)
                    
                    print("The todo is: " + todo.description)
                }
                
            case .failure(let error):
                print("Error")
                
            }
            
        }
        
    }
    
    
    //Pruebas con apiari
    @IBAction func postApiari(_ sender: AnyObject) {
        
        Alamofire.request("https://private-2595c-getmeallmyreviews13.apiary-mock.com/question2",  method: .post).responseJSON { response in
            
            
            switch response.result {
            case .success:
                print("Validation Successful")
                let json = JSON(data: response.data!)
                if let value = response.result.value {
                    let todo = JSON(value)
                    print("The todo is: " + todo.description)
                }
                var i = 1
                
                
                
            case .failure(let error):
                print("Error")
                
            }
            
            
            
        }
        
        
        
    }
 */
    

}

